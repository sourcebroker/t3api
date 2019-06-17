<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Handler;

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
        return array_map(function ($supportedType) {
            return [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type' => $supportedType,
                'format' => 'json',
                'method' => 'serialize',
            ];
        }, static::$supportedTypes);
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
