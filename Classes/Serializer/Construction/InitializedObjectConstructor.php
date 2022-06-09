<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Construction;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

/**
 * Class InitializedObjectConstructor
 *
 * Object constructor that allows deserialization into already constructed
 * objects passed through the deserialization context
 */
class InitializedObjectConstructor implements ObjectConstructorInterface
{
    /**
     * {@inheritdoc}
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        if ($context->hasAttribute('target') && 1 === $context->getDepth()) {
            return $context->getAttribute('target');
        }

        return null;
    }
}
