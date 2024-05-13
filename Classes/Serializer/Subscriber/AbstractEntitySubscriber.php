<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Serializer\Handler\AbstractDomainObjectHandler;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AbstractEntitySubscriber
 */
class AbstractEntitySubscriber implements EventSubscriberInterface
{
    public function __construct(protected readonly ApiResourceRepository $apiResourceRepository) {}

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
            ],
            [
                'event' => Events::PRE_DESERIALIZE,
                'method' => 'onPreDeserialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        if (!$event->getObject() instanceof AbstractDomainObject) {
            return;
        }

        /** @var AbstractDomainObject $entity */
        $entity = $event->getObject();

        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        $this->addForceEntityProperties($entity, $visitor);
        $this->addIri($entity, $visitor);
    }

    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        // Changes type to the custom one to make it possible to handle data with serializer handler
        if (
            !isset($event->getType()['params']['_skipDomainObjectTransport'])
            && is_subclass_of($event->getType()['name'], AbstractDomainObject::class)
            && $event->getContext()->getDepth() > 1
        ) {
            $event->setType(
                AbstractDomainObjectHandler::TYPE,
                [
                    'targetType' => $event->getType()['name'],
                ]
            );
        }
    }

    protected function addForceEntityProperties(AbstractDomainObject $entity, JsonSerializationVisitor $visitor): void
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['forceEntityProperties'] as $property) {
            $value = ObjectAccess::getProperty($entity, $property);
            $visitor->visitProperty(
                new StaticPropertyMetadata(AbstractDomainObject::class, $property, $value),
                $value
            );
        }
    }

    protected function addIri(AbstractDomainObject $entity, JsonSerializationVisitor $visitor): void
    {
        $apiResource = $this->apiResourceRepository->getByEntity($entity);
        if ($apiResource instanceof ApiResource && $apiResource->getMainItemOperation() instanceof ItemOperation) {
            // @todo should be generated with symfony router
            $iri = str_replace(
                '{id}',
                (string)$entity->getUid(),
                $apiResource->getMainItemOperation()->getRoute()->getPath()
            );
            $visitor->visitProperty(
                new StaticPropertyMetadata(AbstractDomainObject::class, '@id', $iri),
                $iri
            );
        }
    }
}
