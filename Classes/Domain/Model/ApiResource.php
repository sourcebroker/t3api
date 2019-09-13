<?php

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
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
     * @var Pagination
     */
    protected $pagination;

    /**
     * @var PersistenceSettings
     */
    protected $persistenceSettings;

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

        $this->pagination = new Pagination($apiResourceAnnotation);
        $this->persistenceSettings = new PersistenceSettings($apiResourceAnnotation);
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
     * @return ItemOperation|null
     *
     * @todo for now first item operation is treated as main, maybe in future it should be configurable
     */
    public function getMainItemOperation(): ?ItemOperation
    {
        if (!empty($this->getItemOperations())) {
            $itemOperations = $this->getItemOperations();
            return array_shift($itemOperations);
        }

        return null;
    }

    /**
     * @return CollectionOperation[]
     */
    public function getCollectionOperations(): array
    {
        return $this->collectionOperations;
    }

    /**
     * @return CollectionOperation|null
     *
     * @todo for now first collection operation is treated as main, maybe in future it should be configurable
     */
    public function getMainCollectionOperation(): ?CollectionOperation
    {
        if (!empty($this->getCollectionOperations())) {
            $collectionOperations = $this->getCollectionOperations();
            return array_shift($collectionOperations);
        }

        return null;
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

    /**
     * @return Pagination
     */
    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    /**
     * @return PersistenceSettings
     */
    public function getPersistenceSettings(): PersistenceSettings
    {
        return $this->persistenceSettings;
    }
}
