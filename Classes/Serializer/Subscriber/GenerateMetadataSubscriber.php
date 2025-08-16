<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use SourceBroker\T3api\Attribute\AsSerializerSubscriber;
use SourceBroker\T3api\Service\SerializerMetadataService;

#[AsSerializerSubscriber]
class GenerateMetadataSubscriber implements EventSubscriberInterface
{
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
     * @throws \ReflectionException
     */
    public function onPreSerialize(ObjectEvent $event): void
    {
        if (class_exists($event->getType()['name'])) {
            SerializerMetadataService::generateAutoloadForClass($event->getType()['name']);
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        if (class_exists($event->getType()['name'])) {
            SerializerMetadataService::generateAutoloadForClass($event->getType()['name']);
        }
    }
}
