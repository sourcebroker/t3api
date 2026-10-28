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
use SourceBroker\T3api\Serializer\Handler\CurrentFeUserHandler;

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
        $data = $event->getData();

        if (!is_array($data) || !class_exists($className)) {
            return;
        }

        /** @var ClassHierarchyMetadata|MergeableClassMetadata|null $metadata */
        $metadata = $event->getContext()->getMetadataFactory()->getMetadataForClass($event->getType()['name']);

        if (!$metadata instanceof ClassMetadata) {
            return;
        }

        foreach ($metadata->propertyMetadata as $propertyName => $propertyMetadata) {
            if ($propertyMetadata->type === null || $propertyMetadata->type['name'] !== CurrentFeUserHandler::TYPE) {
                continue;
            }

            // JMS skips handlers for missing or null values. Prepare the serialized field name
            // with a non-null placeholder; the handler resolves the user from TYPO3 Context.
            $data[$propertyMetadata->serializedName ?? $propertyName] = 0;
        }

        $event->setData($data);
    }
}
