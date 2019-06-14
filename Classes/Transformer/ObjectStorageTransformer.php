<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ObjectStorageTransformer
 *
 * @package SourceBroker\Restify\Transformator
 */
class ObjectStorageTransformer implements TransformerInterface
{
    /**
     * @param ObjectStorage $objectStorage
     *
     * @return array
     */
    public function serialize($objectStorage)
    {
        return $objectStorage->toArray();
    }
}
