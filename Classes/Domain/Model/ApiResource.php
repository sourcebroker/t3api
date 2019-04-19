<?php

namespace SourceBroker\Restify\Domain\Model;

use SourceBroker\Restify\Annotation\ApiResource as ApiResourceAnnotation;

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
     * @param string $entity
     * @param ApiResourceAnnotation $apiResourceAnnotation
     */
    public function __construct(string $entity, ApiResourceAnnotation $apiResourceAnnotation)
    {
        $this->entity = $entity;

        foreach ($apiResourceAnnotation->getItemOperations() as $operationKey => $operationData) {
            $this->itemOperations[] = new ItemOperation($operationKey, $operationData);
        }

        foreach ($apiResourceAnnotation->getCollectionOperations() as $operationKey => $operationData) {
            $this->collectionOperations[] = new CollectionOperation($operationKey, $operationData);
        }
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
}
