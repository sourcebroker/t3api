<?php

namespace SourceBroker\Restify\Annotation;

/**
 * ApiResource annotation.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes(
 *     @Attribute("collectionOperations", type="array"),
 *     @Attribute("itemOperations", type="array"),
 * )
 */
class ApiResource
{

    /**
     * @var array
     */
    protected $itemOperations = [];

    /**
     * @var array
     */
    protected $collectionOperations = [];

    /**
     * ApiResource constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->itemOperations = $values['itemOperations'] ?? [];
        $this->collectionOperations = $values['collectionOperations'] ?? [];
    }

    /**
     * @return array
     */
    public function getItemOperations(): array
    {
        return $this->itemOperations;
    }

    /**
     * @return array
     */
    public function getCollectionOperations(): array
    {
        return $this->collectionOperations;
    }
}
