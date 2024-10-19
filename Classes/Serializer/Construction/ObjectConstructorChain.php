<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Construction;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use SourceBroker\T3api\Factory\SerializerObjectConstructorCollection;

class ObjectConstructorChain implements ObjectConstructorInterface
{
    public function __construct(protected SerializerObjectConstructorCollection $serializerObjectConstructorCollection) {}

    /**
     * @inheritDoc
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        foreach ($this->serializerObjectConstructorCollection->get() as $constructor) {
            $object = $constructor->construct($visitor, $metadata, $data, $type, $context);

            if ($object !== null) {
                return $object;
            }
        }

        throw new \RuntimeException(sprintf('Could not construct object `%s`', $metadata->name), 1577822761813);
    }
}
