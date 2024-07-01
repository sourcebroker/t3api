<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use Symfony\Component\Routing\RouteCollection;

class ApiResource
{
    protected string $entity;

    /**
     * @var ItemOperation[]
     */
    protected array $itemOperations = [];

    /**
     * @var CollectionOperation[]
     */
    protected array $collectionOperations = [];

    protected RouteCollection $routes;

    /**
     * @var OperationInterface[]
     */
    protected array $routeNameToOperation = [];

    protected Pagination $pagination;

    protected PersistenceSettings $persistenceSettings;

    protected UploadSettings $uploadSettings;

    public function __construct(string $entity, ApiResourceAnnotation $apiResourceAnnotation)
    {
        $this->entity = $entity;
        $this->routes = new RouteCollection();

        $attributes = $apiResourceAnnotation->getAttributes();
        $this->pagination = Pagination::create($attributes);
        $this->persistenceSettings = PersistenceSettings::create($attributes['persistence'] ?? []);
        $this->uploadSettings = UploadSettings::create($attributes['upload'] ?? []);

        foreach ($apiResourceAnnotation->getItemOperations() as $operationKey => $operationData) {
            $this->itemOperations[] = new ItemOperation($operationKey, $this, $operationData);
        }

        foreach ($apiResourceAnnotation->getCollectionOperations() as $operationKey => $operationData) {
            $this->collectionOperations[] = new CollectionOperation($operationKey, $this, $operationData);
        }

        foreach ($this->getOperations() as $operation) {
            $routeName = spl_object_hash($operation);
            $this->routes->add($routeName, $operation->getRoute());
            $this->routeNameToOperation[spl_object_hash($operation)] = $operation;
        }
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return OperationInterface[]
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
     * @todo for now first item operation is treated as main, maybe in future it should be configurable
     */
    public function getMainItemOperation(): ?ItemOperation
    {
        if ($this->getItemOperations() !== []) {
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
     * @todo for now first collection operation is treated as main, maybe in future it should be configurable
     */
    public function getMainCollectionOperation(): ?CollectionOperation
    {
        if ($this->getCollectionOperations() !== []) {
            $collectionOperations = $this->getCollectionOperations();

            return array_shift($collectionOperations);
        }

        return null;
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    public function getOperationByRouteName(string $routeName): OperationInterface
    {
        if (!isset($this->routeNameToOperation[$routeName])) {
            throw new \InvalidArgumentException(sprintf('Operation for %s not found', $routeName), 1557217180348);
        }

        return $this->routeNameToOperation[$routeName];
    }

    public function addFilter(ApiFilter $apiFilter): void
    {
        foreach ($this->getCollectionOperations() as $collectionOperation) {
            $collectionOperation->addFilter($apiFilter);
        }
    }

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public function getPersistenceSettings(): PersistenceSettings
    {
        return $this->persistenceSettings;
    }

    public function getUploadSettings(): UploadSettings
    {
        return $this->uploadSettings;
    }
}
