<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use ReflectionException;
use SourceBroker\T3api\Service\SerializerMetadataService;

/**
 * Class GenerateMetadataSubscriber
 */
class GenerateMetadataSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPreSerialize',
            ],
            [
                'event' => Events::PRE_DESERIALIZE,
                'method' => 'onPreDeserialize',
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     *
     * @throws ReflectionException
     */
    public function onPreSerialize(ObjectEvent $event): void
    {
        if (class_exists($event->getType()['name'])) {
            SerializerMetadataService::generateAutoloadForClass($event->getType()['name']);
        }
    }

    /**
     * @param PreDeserializeEvent $event
     *
     * @throws ReflectionException
     */
    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        if (class_exists($event->getType()['name'])) {
            SerializerMetadataService::generateAutoloadForClass($event->getType()['name']);
        }
    }
}
