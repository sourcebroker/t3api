<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use InvalidArgumentException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ObjectStorageHandler
 */
class ObjectStorageHandler extends AbstractHandler implements SerializeHandlerInterface, DeserializeHandlerInterface
{
    /**
     * @var string[]
     */
    protected static $supportedTypes = [
        ObjectStorage::class,
        LazyObjectStorage::class,
    ];

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
    ): array {
        $type['name'] = 'array';

        $context->stopVisiting($objectStorage);
        $result = $visitor->visitArray($objectStorage->toArray(), $type);
        $context->startVisiting($objectStorage);

        return $result;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $data
     * @param array $type
     * @param DeserializationContext $context
     *
     * @return ObjectStorage
     */
    public function deserialize(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context
    ): ObjectStorage {
        $objectStorage = new ObjectStorage();

        if (empty($data)) {
            return $objectStorage;
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Data of type `%s` can not be converted to %s in path `%s`',
                    gettype($data),
                    ObjectStorage::class,
                    implode('.', $context->getCurrentPath())
                ),
                1570805126535
            );
        }

        $type['name'] = 'array';

        $items = $visitor->visitArray($data, $type);

        foreach ($items as $item) {
            $objectStorage->attach($item);
        }

        return $objectStorage;
    }
}
