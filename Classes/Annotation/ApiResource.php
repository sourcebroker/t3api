<?php

namespace SourceBroker\T3Api\Annotation;

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
     * @var array
     */
    protected $attributes = [];

    /**
     * ApiResource constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->itemOperations = $values['itemOperations'] ?? $this->itemOperations;
        $this->collectionOperations = $values['collectionOperations'] ?? $this->collectionOperations;
        $this->attributes = array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination'], $values['attributes'] ?? []);
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

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
