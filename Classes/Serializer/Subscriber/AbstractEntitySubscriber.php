<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\JsonSerializationVisitor;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AbstractEntitySubscriber
 */
class AbstractEntitySubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        if (!$event->getObject() instanceof AbstractEntity) {
            return;
        }

        /** @var AbstractEntity $entity */
        $entity = $event->getObject();

        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['forceEntityProperties'] as $property) {
            if (!$visitor->hasData($property)) {
                $visitor->setData($property, ObjectAccess::getProperty($entity, $property));
            }
        }
    }
}
