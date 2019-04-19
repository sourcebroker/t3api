<?php

namespace SourceBroker\Restify\Dispatcher;

use Doctrine\Common\Annotations\AnnotationReader;
use SourceBroker\Restify\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\Restify\Domain\Model\AbstractOperation;
use SourceBroker\Restify\Domain\Model\ApiResource;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class Bootstrap
 */
class Bootstrap
{
    public function process()
    {
        $apiResources = $this->getAllApiResources();
        $currentRoute = $this->getCurrentRoute();

        foreach ($apiResources as $apiResource) {
            /** @var AbstractOperation[] $operations */
            $operations = array_merge($apiResource->getItemOperations(), $apiResource->getCollectionOperations());

            foreach ($operations as $operation) {
                DebuggerUtility::var_dump($operation);
                if ($this->routeMatchesOperation($currentRoute, $operation)) {
                    $this->processOperation($apiResource, $operation);
                }
            }
        }

        die('NO ROUTE FOUND');
    }

    /**
     * @param AbstractOperation $operation
     */
    private function processOperation(ApiResource $apiResource, AbstractOperation $operation)
    {
        DebuggerUtility::var_dump($apiResource, 'THIS RESOURCE WILL BE PROCESSED');
        DebuggerUtility::var_dump($operation, 'THIS OPERATION WILL BE PROCESSED');
        die();
    }

    /**
     * @param string $route
     * @param AbstractOperation $operation
     * @todo move to more appropriate place
     * @todo implement support for routes with params
     */
    private function routeMatchesOperation(string $route, AbstractOperation $operation): bool
    {
        return $operation->getPath() === $route;
    }

    /**
     * @return mixed
     */
    private function getCurrentRoute()
    {
        return '/' . preg_replace('/^' . preg_quote(RESTIFY_BASE_PATH, '/') . '/', '', $_SERVER['REQUEST_URI']);
    }

    /**
     * @return ApiResource[]
     * @todo move to more appropriate place
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
     */
    private function getAllDomainModels()
    {
        foreach (glob(PATH_site.'typo3conf/ext/*/Classes/Domain/Model/*.php') as $domainModelClassFile) {
            require_once $domainModelClassFile;
        }

        return array_filter(
            get_declared_classes(),
            function($class) {
                return is_subclass_of($class, AbstractEntity::class);
            }
        );
    }
}
