<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ObjectStorageTransformer
 */
class ObjectStorageTransformer extends AbstractTransformer
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
