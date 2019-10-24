<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\Domain\Model\AbstractOperation;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Server;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use SourceBroker\T3api\Filter\OrderFilter;
use SourceBroker\T3api\Response\AbstractCollectionResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class OpenApiBuilder
 */
class OpenApiBuilder
{

    /**
     * @param string $basePath
     * @param ApiResource[] $apiResources
     *
     * @return OpenApi
     *
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     */
    public static function build(string $basePath, array $apiResources): OpenApi
    {
        return OpenApi::create()
            ->openapi(OpenApi::OPENAPI_3_0_2)
            ->servers(...self::getServers())
            ->info(self::getInfo())
            ->tags(...self::getTags($apiResources))
            ->paths(...self::getPaths($apiResources))
        ;
    }

    /**
     * @return Info
     */
    protected static function getInfo(): Info
    {
        return Info::create()
            ->title(sprintf('REST API of %s', GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')))
            // @todo make version configurable
            ->version('1.0.0');
    }

    /**
     * @return array
     */
    protected static function getServers(): array
    {
        return [
            Server::create()
                ->url('/' . RouteService::getApiBasePath()),
        ];
    }

    /**
     * @param ApiResource[] $apiResources
     *
     * @return array
     */
    protected static function getTags(array $apiResources): array
    {
        return array_map('self::getTag', $apiResources);
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
     * @return PathItem[]
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
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
     * @param AbstractOperation $apiOperation
     *
     * @return Operation
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     */
    protected static function getOperation(AbstractOperation $apiOperation): Operation
    {
        $summary = null;
        if ($apiOperation instanceof ItemOperation && $apiOperation->getMethod() === 'GET') {
            $summary = 'Retrieves the resource.';
        } elseif ($apiOperation instanceof CollectionOperation && $apiOperation->getMethod() === 'GET') {
            $summary = 'Retrieves the collection of resources.';
        } elseif ($apiOperation instanceof CollectionOperation && $apiOperation->getMethod() === 'POST') {
            $summary = 'Creates the resource.';
        } elseif ($apiOperation instanceof ItemOperation && $apiOperation->getMethod() === 'PUT') {
            $summary = 'Replaces the resource';
        } elseif ($apiOperation instanceof ItemOperation && $apiOperation->getMethod() === 'PATCH') {
            $summary = 'Updates the resource';
        } elseif ($apiOperation instanceof ItemOperation && $apiOperation->getMethod() === 'DELETE') {
            $summary = 'Removes the resource';
        }

        $parameters = self::getOperationParameters($apiOperation);
        $responses = self::getOperationResponses($apiOperation);

        return Operation::create()
            ->tags(self::getTag($apiOperation->getApiResource()))
            ->action(constant(Operation::class . '::ACTION_' . $apiOperation->getMethod()))
            ->summary($summary)
            ->parameters(...$parameters)
            ->responses(...$responses);
    }

    /**
     * @param AbstractOperation $operation
     *
     * @return Parameter[]
     */
    protected static function getOperationParameters(AbstractOperation $operation): array
    {
        $pathParameters = [];
        $orderParameters = [];
        $filterParameters = [];

        if ($operation instanceof ItemOperation && strpos($operation->getPath(), '{id}') !== false) {
            $pathParameters[] = Parameter::create()
                ->name('id')
                ->in(Parameter::IN_PATH)
                ->description(sprintf('ID of %s', $operation->getApiResource()->getEntity()))
                ->required(true)
                ->schema(Schema::integer());
        }

        if ($operation instanceof CollectionOperation) {
            foreach ($operation->getFilters() as $filter) {
                if (is_subclass_of($filter->getFilterClass(), OrderFilter::class)) {
//                    $orderParameters[] = Parameter::create()
//                        ->name($filter->getParameterName() . '[' . $filter->getProperty() . ']')
//                        ->schema(S)
//                    ;
                } else {
                    $filterParameters[] = Parameter::create()
                        ->name($filter->getParameterName())
                        ->in(Parameter::IN_QUERY);
                }
            }
        }

        return array_merge($pathParameters, $orderParameters, $filterParameters);
    }

    /**
     * @param AbstractOperation $operation
     *
     * @return Response[]
     */
    protected static function getOperationResponses(AbstractOperation $operation): array
    {
        $responses = [
            Response::create()
                ->description('Successful operation')
                ->content(
                    MediaType::json()->schema(self::getOperationSchema($operation))
                )
                ->statusCode($operation->getMethod() === 'POST' ? 201 : 200)
        ];

        if ($operation instanceof ItemOperation) {
            $responses[] = Response::create()
                ->statusCode(404)
                ->description('Item not found');
        }

        // @todo #593 validation errors

        return $responses;
    }

    /**
     * @param AbstractOperation $operation
     *
     * @return Schema
     */
    protected static function getOperationSchema(AbstractOperation $operation): Schema
    {
        if ($operation instanceof CollectionOperation) {
            /** @var AbstractCollectionResponse $collectionResponseClass */
            $collectionResponseClass = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'];

            // @todo use real ref instead of entity name or maybe whole schema instead of ref?
            return $collectionResponseClass::getOpenApiSchema($operation->getApiResource()->getEntity());
        }

        return Schema::object();
    }
}
