<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * Class AbstractHandler
 */
abstract class AbstractHandler implements SubscribingHandlerInterface
{
    /**
     * @var string[]
     */
    protected static $supportedTypes = [];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return array_merge(
            ...array_map(
                function ($supportedType) {
                    $methods = [];

                    if (is_subclass_of(static::class, SerializeHandlerInterface::class)) {
                        $methods[] = [
                            'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                            'type' => $supportedType,
                            'format' => 'json',
                            'method' => 'serialize',
                        ];
                    }

                    if (is_subclass_of(static::class, DeserializeHandlerInterface::class)) {
                        $methods[] = [
                            'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                            'type' => $supportedType,
                            'format' => 'json',
                            'method' => 'deserialize',
                        ];
                    }

                    return $methods;
                },
                static::$supportedTypes
            )
        );
    }

    /**
     * @param SerializationVisitorInterface $visitor
     * @param mixed $fileReference
     * @param array $type
     * @param SerializationContext $context
     *
     * @return array
     */
    abstract public function serialize(
        SerializationVisitorInterface $visitor,
        $fileReference,
        array $type,
        SerializationContext $context
    );
}
