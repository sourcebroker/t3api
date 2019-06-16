<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ObjectStorageHandler
 */
class ObjectStorageHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type' => ObjectStorage::class,
                'format' => 'json',
                'method' => 'serialize',
            ],
        ];
    }

    /**
     * @return array|\ArrayObject
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        ObjectStorage $objectStorage,
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
