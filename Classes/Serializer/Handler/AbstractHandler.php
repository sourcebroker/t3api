<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;

abstract class AbstractHandler implements SubscribingHandlerInterface
{
    /**
     * @var string[]
     */
    protected static $supportedTypes = [];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods(): array
    {
        return array_merge(
            ...array_map(
                static function ($supportedType) {
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

    protected function cloneDeserializationContext(
        DeserializationContext $context,
        array $attributes = []
    ): DeserializationContext {
        try {
            $reflection = new \ReflectionClass(Context::class);
            $property = $reflection->getProperty('attributes');
            $property->setAccessible(true);
            $contextAttributes = $property->getValue($context);
            $deserializationContext = DeserializationContext::create();
            foreach (array_merge($contextAttributes, $attributes) as $attributeName => $attributeValue) {
                $deserializationContext->setAttribute($attributeName, $attributeValue);
            }

            return $deserializationContext;
        } catch (\ReflectionException $e) {
            throw new \RuntimeException('Could not clone deserialization object', 1589868671607, $e);
        }
    }

    protected function getDecodedParams(array $params): array
    {
        return array_map(
            '\SourceBroker\T3api\Service\SerializerMetadataService::decodeFromSingleHandlerParam',
            $params
        );
    }
}
