<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Dispatcher;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use SourceBroker\Restify\Accessor\AccessorStrategy;
use SourceBroker\Restify\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\Restify\Domain\Model\AbstractOperation;
use SourceBroker\Restify\Domain\Model\ApiResource;
use SourceBroker\Restify\Domain\Model\CollectionOperation;
use SourceBroker\Restify\Domain\Model\ItemOperation;
use SourceBroker\Restify\Domain\Repository\CommonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Bootstrap
 */
class Bootstrap
{
    /**
     * @throws RouteNotFoundException
     */
    public function process()
    {
        $apiResources = $this->getAllApiResources();
        $context = (new RequestContext())->fromRequest(Request::createFromGlobals());

        foreach ($apiResources as $apiResource) {
            try {
                $urlMatcher = new UrlMatcher($apiResource->getRoutes(), $context);
                $matchedRoute = $urlMatcher->match($context->getPathInfo());
                $this->processOperation($apiResource->getOperationByRouteName($matchedRoute['_route']), $matchedRoute);
            } catch (ResourceNotFoundException $resourceNotFoundException) {
            }
        }

        throw new RouteNotFoundException('Restify resource not found for current route', 1557217186441);
    }

    /**
     * @param AbstractOperation $operation
     * @param array $matchedRoute
     * @throws \Exception
     */
    private function processOperation(AbstractOperation $operation, array $matchedRoute)
    {
        $repository = GeneralUtility::makeInstance(ObjectManager::class)->get(CommonRepository::class);
        $repository->setObjectType($operation->getApiResource()->getEntity());

        if ($operation instanceof ItemOperation) {
            $result = $repository->findByUid((int)$matchedRoute['id']);

            if (!$result) {
                // @todo throw appropriate exception
                throw new \InvalidArgumentException('Item not found');
            }
        } else if ($operation instanceof CollectionOperation) {
            $result = $repository->findAll()->toArray();
        } else {
            // @todo throw appropriate exception
            throw new \Exception('Unknown operation', 1557506987081);
        }

        // @todo avoid die if easily possible
        die($this->getSerializer($operation)->serialize($result, 'json'));
    }

    /**
     * @return ApiResource[]
     * @todo move to more appropriate place
     * @todo add caching
     */
    private function getAllApiResources()
    {
        $domainModels = $this->getAllDomainModels();
        $annotationReader = new AnnotationReader();
        $apiResources = [];

        foreach ($domainModels as $domainModel) {
            /** @var ApiResourceAnnotation $apiResourceAnnotation */
            $apiResourceAnnotation = $annotationReader->getClassAnnotation(
                new \ReflectionClass($domainModel),
                ApiResourceAnnotation::class
            );

            if (!$apiResourceAnnotation) {
                continue;
            }

            $apiResources[] = new ApiResource($domainModel, $apiResourceAnnotation);
        }

        return $apiResources;
    }

    /**
     * @return string[]
     * @todo move to more appropriate place
     * @todo add caching
     */
    private function getAllDomainModels()
    {
        foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extKey) {
            $extPath = ExtensionManagementUtility::extPath($extKey);
            foreach (glob($extPath.'Classes/Domain/Model/*.php') as $domainModelClassFile) {
                require_once $domainModelClassFile;
            }
        }

        return array_filter(
            get_declared_classes(),
            function($class) {
                return is_subclass_of($class, AbstractEntity::class);
            }
        );
    }

    /**
     * @param AbstractOperation $operation
     * @return SerializerInterface
     * @todo refactor - move to appropriate place
     */
    private function getSerializer(AbstractOperation $operation): SerializerInterface
    {
        // @todo use TYPO3 API to create directory
        $cacheDirectory = PATH_site.'/typo3temp/var/cache/code/restify/jms-serializer';
        mkdir($cacheDirectory, 0777, true);
        GeneralUtility::fixPermissions($cacheDirectory);

        return SerializerBuilder::create()
            ->setCacheDir($cacheDirectory)
            ->setDebug(GeneralUtility::getApplicationContext()->isDevelopment())
            ->setSerializationContextFactory(function() use ($operation) {
                $serializationContext = SerializationContext::create()
                    ->setSerializeNull(true);

                if (!empty($operation->getContextGroups())) {
                    $serializationContext->setGroups($operation->getContextGroups());
                }

                return $serializationContext;
            })
            ->configureHandlers(function(HandlerRegistry $registry) {
                $registry->registerHandler(
                    \JMS\Serializer\GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    ObjectStorage::class,
                    'json',
                    function($visitor, ObjectStorage $objectStorage, array $type) {
                        return $objectStorage->toArray();
                    }
                );
            })
            ->addDefaultHandlers()
            ->setAccessorStrategy(new AccessorStrategy())
            ->setPropertyNamingStrategy(
                new SerializedNameAnnotationStrategy(
                    new IdenticalPropertyNamingStrategy()
                )
            )
            ->build();
    }
}
