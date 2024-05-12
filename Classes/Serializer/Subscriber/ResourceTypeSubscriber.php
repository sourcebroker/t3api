<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

class ResourceTypeSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        if (!$event->getObject() instanceof AbstractDomainObject) {
            return;
        }

        $entity = $event->getObject();
        $visitor = $event->getVisitor();

        $type = get_class($entity);
        $visitor->visitProperty(
            new StaticPropertyMetadata(AbstractDomainObject::class, '@type', $type),
            $type
        );
    }
}
