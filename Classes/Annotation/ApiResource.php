<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;

/**
 * ApiResource annotation.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes(
 *     @Attribute("collectionOperations", type="array"),
 *     @Attribute("itemOperations", type="array"),
 *     @Attribute("attributes", type="array"),
 * )
 */
class ApiResource
{
    protected array $itemOperations = [];

    protected array $collectionOperations = [];

    protected array $attributes = [];

    public function __construct(array $values = [])
    {
        $this->itemOperations = $values['itemOperations'] ?? $this->itemOperations;
        $this->collectionOperations = $values['collectionOperations'] ?? $this->collectionOperations;
        $this->attributes = array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination'] ?? [],
            $values['attributes'] ?? []
        );
    }

    public function getItemOperations(): array
    {
        return $this->itemOperations;
    }

    public function getCollectionOperations(): array
    {
        return $this->collectionOperations;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
