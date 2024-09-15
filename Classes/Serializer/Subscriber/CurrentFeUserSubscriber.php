<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Metadata\ClassHierarchyMetadata;
use Metadata\ClassMetadata;
use Metadata\MergeableClassMetadata;
use SourceBroker\T3api\Attribute\AsSerializerSubscriber;
use SourceBroker\T3api\Serializer\Handler\CurrentFeUserHandler;

#[AsSerializerSubscriber]
class CurrentFeUserSubscriber implements EventSubscriberInterface
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

    public function onPreSerialize(PreSerializeEvent $event): void
    {
        if ($event->getType()['name'] !== CurrentFeUserHandler::TYPE) {
            return;
        }

        $event->setType($event->getType()['params'][0]);
    }

    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        $className = $event->getType()['name'];

        if (!class_exists($className)) {
            return;
        }

        /** @var ClassHierarchyMetadata|MergeableClassMetadata|null $metadata */
        $metadata = $event->getContext()->getMetadataFactory()->getMetadataForClass($event->getType()['name']);

        if (!$metadata instanceof ClassMetadata) {
            return;
        }

        $data = $event->getData();
        foreach ($metadata->propertyMetadata as $propertyName => $propertyMetadata) {
            if ($propertyMetadata->type['name'] !== CurrentFeUserHandler::TYPE) {
                continue;
            }

            $data[$propertyName] = $GLOBALS['TSFE']->fe_user->user['uid'] ?? null;
        }

        $event->setData($data);
    }
}
