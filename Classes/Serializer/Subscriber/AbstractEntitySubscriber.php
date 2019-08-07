<?php
declare(strict_types=1);

namespace SourceBroker\T3Api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
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
        if (!$event->getObject() instanceof AbstractDomainObject) {
            return;
        }

        /** @var AbstractDomainObject $entity */
        $entity = $event->getObject();

        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['forceEntityProperties'] as $property) {
            if (!$visitor->hasData($property)) {
                $visitor->setData($property, ObjectAccess::getProperty($entity, $property));
            }
        }
    }
}
