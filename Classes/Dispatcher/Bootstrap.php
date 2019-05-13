<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Dispatcher;

use Doctrine\Common\Annotations\AnnotationReader;
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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
        } else if ($operation instanceof CollectionOperation) {
            $result = $repository->findAll();
        } else {
            // @todo throw appropriate exception
            throw new \Exception('Unknown operation', 1557506987081);
        }

        DebuggerUtility::var_dump($operation, 'THIS OPERATION WILL BE PROCESSED');
        DebuggerUtility::var_dump($result, 'THIS WILL BE THE RESULT OF RESPONSE');
        die();
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
}
