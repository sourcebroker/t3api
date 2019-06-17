<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ObjectStorageHandler
 */
class ObjectStorageHandler extends AbstractHandler
{
    /**
     * @var string[]
     */
    protected static $supportedTypes = [ObjectStorage::class];

    /**
     * @param SerializationVisitorInterface $visitor
     * @param ObjectStorage $objectStorage
     * @param array $type
     * @param SerializationContext $context
     *
     * @return array
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $objectStorage,
        array $type,
        SerializationContext $context
    ) {
        $type['name'] = 'array';

        $context->stopVisiting($objectStorage);
        $result = $visitor->visitArray($objectStorage->toArray(), $type);
        $context->startVisiting($objectStorage);

        return $result;
    }
}
