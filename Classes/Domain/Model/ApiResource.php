<?php

namespace SourceBroker\Restify\Domain\Model;

use SourceBroker\Restify\Annotation\ApiResource as ApiResourceAnnotation;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ApiResource
 */
class ApiResource
{

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var ItemOperation[]
     */
    protected $itemOperations = [];

    /**
     * @var CollectionOperation[]
     */
    protected $collectionOperations = [];

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var AbstractOperation[]
     */
    protected $routeNameToOperation;

    /**
     * @param string $entity
     * @param ApiResourceAnnotation $apiResourceAnnotation
     */
    public function __construct(string $entity, ApiResourceAnnotation $apiResourceAnnotation)
    {
        $this->entity = $entity;
        $this->routes = new RouteCollection();

        foreach ($apiResourceAnnotation->getItemOperations() as $operationKey => $operationData) {
            $this->itemOperations[] = new ItemOperation($operationKey, $this, $operationData);
        }

        foreach ($apiResourceAnnotation->getCollectionOperations() as $operationKey => $operationData) {
            $this->collectionOperations[] = new CollectionOperation($operationKey, $this, $operationData);
        }

        /** @var AbstractOperation $operation */
        foreach ($this->getOperations() as $operation) {
            $this->routes->add($operation->getRoute()->getPath(), $operation->getRoute());
            $this->routeNameToOperation[$operation->getRoute()->getPath()] = $operation;
        }
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return AbstractOperation[]
     */
    public function getOperations(): array
    {
        return array_merge($this->getItemOperations(), $this->getCollectionOperations());
    }

    /**
     * @return ItemOperation[]
     */
    public function getItemOperations(): array
    {
        return $this->itemOperations;
    }

    /**
     * @return CollectionOperation[]
     */
    public function getCollectionOperations(): array
    {
        return $this->collectionOperations;
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * @param string $routeName
     *
     * @return AbstractOperation
     */
    public function getOperationByRouteName(string $routeName): AbstractOperation
    {
        if (!isset($this->routeNameToOperation[$routeName])) {
            throw new \InvalidArgumentException(sprintf('Operation for %s not found', $routeName), 1557217180348);
        }

        return $this->routeNameToOperation[$routeName];
    }

    /**
     * @param ApiFilter $apiFilter
     */
    public function addFilter(ApiFilter $apiFilter)
    {
        foreach ($this->getCollectionOperations() as $collectionOperation) {
            $collectionOperation->addFilter($apiFilter);
        }
    }
}
