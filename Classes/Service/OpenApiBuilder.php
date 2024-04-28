<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use DateTime;
use Exception;
use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException as OasInvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Components;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Server;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;
use RuntimeException;
use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\ResourceNotFoundException;
use SourceBroker\T3api\Exception\ValidationException;
use SourceBroker\T3api\Filter\OpenApiSupportingFilterInterface;
use SourceBroker\T3api\Response\AbstractCollectionResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class OpenApiBuilder
 */
class OpenApiBuilder
{
    /**
     * @var Components
     */
    protected static $components;

    /**
     * @var ApiResource[]
     */
    protected static $apiResources = [];

    /**
     * @param ApiResource[] $apiResources
     *
     * @throws OasInvalidArgumentException
     * @return OpenApi
     */
    public static function build(array $apiResources): OpenApi
    {
        self::$apiResources = $apiResources;
        self::$components = Components::create();

        return OpenApi::create()
            ->openapi(OpenApi::OPENAPI_3_0_2)
            ->info(self::getInfo())
            ->servers(...self::getServers())
            ->tags(...self::getTags($apiResources))
            ->paths(...self::getPaths($apiResources))
            ->components(self::$components);
    }

    private static function getInfo(): Info
    {
        $siteConfig = SiteService::getCurrent()->getConfiguration();
        $info = new Info();
        return $info
            ->title($siteConfig['routeEnhancers']['T3api']['title'] ?? '')
            ->description($siteConfig['routeEnhancers']['T3api']['description'] ?? '')
            ->version($siteConfig['routeEnhancers']['T3api']['version'] ?? '');
    }

    /**
     * @return array
     */
    protected static function getServers(): array
    {
        return [
            Server::create()
                ->url(RouteService::getFullApiBaseUrl()),
        ];
    }

    /**
     * @param ApiResource[] $apiResources
     *
     * @return array
     */
    protected static function getTags(array $apiResources): array
    {
        return array_map(self::getTag(...), $apiResources);
    }

    /**
     * @param ApiResource $apiResource
     *
     * @return Tag
     */
    protected static function getTag(ApiResource $apiResource): Tag
    {
        // @todo caching because it is used in few places
        return Tag::create()
            ->name($apiResource->getEntity())
            ->description(sprintf('Operations about %s', $apiResource->getEntity()));
    }

    /**
     * @param ApiResource[] $apiResources
     *
     * @throws OasInvalidArgumentException
     * @return PathItem[]
     */
    protected static function getPaths(array $apiResources): array
    {
        $paths = [];

        foreach ($apiResources as $apiResource) {
            foreach ($apiResource->getOperations() as $apiOperation) {
                if (!isset($paths[$apiOperation->getPath()])) {
                    $paths[$apiOperation->getPath()] = [
                        'path' => PathItem::create()->route($apiOperation->getPath()),
                        'operations' => [],
                    ];
                }

                $paths[$apiOperation->getPath()]['operations'][] = self::getOperation($apiOperation);
            }
        }

        return array_values(
            array_map(
                function (array $pathElement) {
                    /** @var PathItem $pathItem */
                    $pathItem = $pathElement['path'];

                    return $pathItem->operations(...$pathElement['operations']);
                },
                $paths
            )
        );
    }

    /**
     * @param OperationInterface $apiOperation
     *
     * @throws OasInvalidArgumentException
     * @return Operation
     */
    protected static function getOperation(OperationInterface $apiOperation): Operation
    {
        $summary = null;
        if ($apiOperation instanceof ItemOperation && $apiOperation->isMethodGet()) {
            $summary = 'Retrieves the resource.';
        } elseif ($apiOperation instanceof CollectionOperation && $apiOperation->isMethodGet()) {
            $summary = 'Retrieves the collection of resources.';
        } elseif ($apiOperation instanceof CollectionOperation && $apiOperation->isMethodPost()) {
            $summary = 'Creates the resource.';
        } elseif ($apiOperation instanceof ItemOperation && $apiOperation->isMethodPut()) {
            $summary = 'Replaces the resource';
        } elseif ($apiOperation instanceof ItemOperation && $apiOperation->isMethodPatch()) {
            $summary = 'Updates the resource';
        } elseif ($apiOperation instanceof ItemOperation && $apiOperation->isMethodDelete()) {
            $summary = 'Removes the resource';
        }

        return Operation::create()
            ->tags(self::getTag($apiOperation->getApiResource()))
            ->action(constant(Operation::class . '::ACTION_' . $apiOperation->getMethod()))
            ->summary($summary)
            ->parameters(...self::getOperationParameters($apiOperation))
            ->responses(...self::getOperationResponses($apiOperation))
            ->requestBody(self::getOperationRequestBody($apiOperation));
    }

    /**
     * @param OperationInterface $operation
     *
     * @throws OasInvalidArgumentException
     * @return Parameter[]
     */
    protected static function getOperationParameters(OperationInterface $operation): array
    {
        return array_merge(
            self::getPathParametersForOperation($operation),
            self::getFilterParametersForOperation($operation),
            self::getPaginationParametersForOperation($operation)
        );
    }

    /**
     * @param OperationInterface $operation
     *
     * @return Parameter[]
     */
    protected static function getPathParametersForOperation(OperationInterface $operation): array
    {
        if (!$operation instanceof ItemOperation || strpos($operation->getPath(), '{id}') === false) {
            return [];
        }

        return [
            Parameter::create()
                ->name('id')
                ->in(Parameter::IN_PATH)
                ->description(sprintf('ID of %s', $operation->getApiResource()->getEntity()))
                ->required(true)
                ->schema(Schema::integer()),
        ];
    }

    /**
     * @param OperationInterface $operation
     *
     * @return Parameter[]
     */
    protected static function getFilterParametersForOperation(OperationInterface $operation): array
    {
        $parameters = [];

        if (!$operation instanceof CollectionOperation || !$operation->isMethodGet()) {
            return $parameters;
        }

        foreach ($operation->getFilters() as $filter) {
            if (!is_subclass_of($filter->getFilterClass(), OpenApiSupportingFilterInterface::class, true)) {
                continue;
            }

            $filterParameters = call_user_func([$filter->getFilterClass(), 'getOpenApiParameters'], $filter);

            /** @var Parameter $filterParameter */
            foreach ($filterParameters as $filterParameter) {
                $parameters[$filterParameter->name] = $filterParameter->in(Parameter::IN_QUERY);
            }
        }

        sort($parameters);

        return $parameters;
    }

    /**
     * @param OperationInterface $operation
     *
     * @throws OasInvalidArgumentException
     * @return Parameter[]
     */
    protected static function getPaginationParametersForOperation(OperationInterface $operation): array
    {
        $parameters = [];

        if (
            !$operation instanceof CollectionOperation
            || !$operation->isMethodGet()
            || (!$operation->getPagination()->isClientEnabled() && !$operation->getPagination()->isServerEnabled())
        ) {
            return [];
        }

        $pagination = $operation->getPagination();

        if ($pagination->isClientEnabled()) {
            $parameters[] = Parameter::create()
                ->name($pagination->getEnabledParameterName())
                ->in(Parameter::IN_QUERY)
                ->schema(
                    Schema::boolean()
                )
                ->description('Enables or disables pagination by client (please notice that pagination state can be configured also on server side).');
        }

        $parameters[] = Parameter::create()
            ->name($pagination->getPageParameterName())
            ->in(Parameter::IN_QUERY)
            ->schema(
                Schema::integer()
                    ->minimum(0)
            )
            ->description('Number of the page to read.');

        if ($pagination->isClientItemsPerPage()) {
            $parameters[] = Parameter::create()
                ->name($pagination->getItemsPerPageParameterName())
                ->in(Parameter::IN_QUERY)
                ->schema(
                    Schema::integer()
                        ->minimum(0)
                        ->maximum($pagination->getMaximumItemsPerPage())
                )
                ->description('Number of items on page.');
        }

        return $parameters;
    }

    /**
     * @param OperationInterface $operation
     *
     * @return Response[]
     */
    protected static function getOperationResponses(OperationInterface $operation): array
    {
        $responses = [
            Response::create()
                ->description('Successful operation')
                ->content(
                    MediaType::json()->schema(self::getOperationSchema($operation))
                )
                ->statusCode($operation->isMethodPost() ? 201 : 200),
        ];

        if ($operation instanceof ItemOperation) {
            $responses[] = ResourceNotFoundException::getOpenApiResponse();
        }

        if ($operation->isMethodPatch() || $operation->isMethodPost() || $operation->isMethodPut()) {
            $responses[] = ValidationException::getOpenApiResponse();
        }

        if ($operation->getSecurity() || $operation->getSecurityPostDenormalize()) {
            $responses[] = OperationNotAllowedException::getOpenApiResponse();
        }

        return $responses;
    }

    protected static function getOperationSchema(OperationInterface $operation): Schema
    {
        if ($operation instanceof CollectionOperation && $operation->isMethodGet()) {
            /** @var AbstractCollectionResponse $collectionResponseClass */
            $collectionResponseClass = Configuration::getCollectionResponseClass();

            return $collectionResponseClass::getOpenApiSchema(
                self::getComponentsSchemaReference($operation->getApiResource()->getEntity())
            );
        }

        return Schema::ref(self::getComponentsSchemaReference($operation->getApiResource()->getEntity()));
    }

    /**
     * @param string $class
     * @param string $mode `READ` or `WRITE`
     *
     * @return string
     */
    protected static function getComponentsSchemaReference(string $class, string $mode = 'READ'): string
    {
        $schemaIdentifier = str_replace('\\', '.', $class) . '__' . $mode;
        $referencePath = '#/components/schemas/' . $schemaIdentifier;

        $definedSchemas = array_map(
            function (Schema $schema) {
                return $schema->objectId;
            },
            self::$components->schemas ?? []
        );

        if (!in_array($schemaIdentifier, $definedSchemas)) {
            self::setComponentsSchema($schemaIdentifier, $class, $mode);
        }

        return $referencePath;
    }

    /**
     * @param string $name
     * @param string $class
     * @param string $mode `READ` or `WRITE`
     * @throws RuntimeException
     */
    protected static function setComponentsSchema(string $name, string $class, string $mode): void
    {
        static $currentlyProcessedClasses;

        if (is_null($currentlyProcessedClasses)) {
            $currentlyProcessedClasses = [];
        }

        if (in_array($class, $currentlyProcessedClasses)) {
            return;
        }

        $currentlyProcessedClasses[] = $class;
        $properties = [];

        /** @var ClassMetadata $metadata */
        try {
            SerializerMetadataService::generateAutoloadForClass($class);
            $metadata = self::getMetadataFactory()->getMetadataForClass($class);

            if ($metadata === null) {
                throw new RuntimeException(
                    sprintf('Could not generate metadata for class `%s`', $class),
                    1577637116148
                );
            }
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf('An error occurred while generating metadata for class `%s`', $class),
                1577637267693,
                $e
            );
        }

        /** @var PropertyMetadata $propertyMetadata */
        foreach ($metadata->propertyMetadata as $propertyMetadata) {
            if ($propertyMetadata->class !== $class) {
                continue;
            }

            $properties[] = self::getPropertySchemaFromPropertyMetadata($propertyMetadata, $mode);
        }

        if (self::isApiResourceClass($class) && $mode === 'READ') {
            $properties[] = Schema::string('@id');
        }

        $schemas = self::$components->schemas ?? [];
        $schemas[] = Schema::object($name)
            ->properties(...$properties);

        unset($currentlyProcessedClasses[array_search($class, $currentlyProcessedClasses)]);

        self::$components = self::$components->schemas(...$schemas);
    }

    /**
     * @return MetadataFactoryInterface
     */
    protected static function getMetadataFactory(): MetadataFactoryInterface
    {
        static $metadataFactory;

        if (empty($metadataFactory)) {
            $metadataFactory = GeneralUtility::makeInstance(SerializerService::class)->getMetadataFactory();
        }

        return $metadataFactory;
    }

    /**
     * @param PropertyMetadata $propertyMetadata
     * @param string $mode `READ` or `WRITE`
     *
     * @return Schema
     */
    protected static function getPropertySchemaFromPropertyMetadata(
        PropertyMetadata $propertyMetadata,
        string $mode
    ): Schema {
        $schema = self::getPropertySchemaFromPropertyType(
            $propertyMetadata->type['name'] ?? '',
            $mode,
            $propertyMetadata->type['params'] ?? []
        )->readOnly($propertyMetadata->readOnly);

        return $schema->objectId($propertyMetadata->serializedName);
    }

    /**
     * @param string $type
     * @param string $mode `READ` or `WRITE`
     * @param array $params
     *
     * @return Schema
     */
    protected static function getPropertySchemaFromPropertyType(string $type, string $mode, array $params = []): Schema
    {
        if (is_a($type, ObjectStorage::class, true) && !empty($params[0]['name'])) {
            $schema = Schema::array()->items(self::getPropertySchemaFromPropertyType($params[0]['name'], $mode));
        } elseif (is_a($type, DateTime::class, true)) {
            try {
                $schema = Schema::string()->example((new DateTime())->format(PHP_VERSION_ID >= 70300 ? DateTime::RFC3339_EXTENDED : 'Y-m-d\TH:i:s.uP'));
            } catch (Exception $e) {
                // no chance exception will occur - catch it only to avoid IDE's complaints
            }
        } elseif (class_exists($type)) {
            if ($mode === 'WRITE') {
                $schema = Schema::number()->example(rand(1, 100));
            } else {
                // NOTICE! because of a bug https://github.com/swagger-api/swagger-ui/issues/3325 reference to itself
                // will not be displayed correctly
                $schema = Schema::ref(self::getComponentsSchemaReference($type, $mode));
            }
        } elseif (in_array($type, ['int', 'integer'])) {
            $schema = Schema::integer();
        } elseif (in_array($type, ['string'])) {
            $schema = Schema::string();
        } elseif (in_array($type, ['double', 'float'])) {
            $schema = Schema::number();
        } elseif (in_array($type, ['boolean', 'bool'])) {
            $schema = Schema::boolean();
        }

        return $schema ?? Schema::string();
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    protected static function isApiResourceClass(string $className): bool
    {
        foreach (self::$apiResources as $apiResource) {
            if ($apiResource->getEntity() === $className) {
                return true;
            }
        }

        return false;
    }

    protected static function getOperationRequestBody(OperationInterface $operation): ?RequestBody
    {
        if ($operation->isMethodGet() || $operation->isMethodDelete()) {
            return null;
        }

        return RequestBody::create()
            ->content(
                MediaType::json()->schema(
                    Schema::ref(
                        self::getComponentsSchemaReference($operation->getApiResource()->getEntity(), 'WRITE')
                    )
                )
            );
    }
}
