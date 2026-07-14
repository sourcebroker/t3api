<?php

declare(strict_types=1);

namespace SourceBroker\T3api\EventListener;

use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Event\RecordDeletedEvent;
use SourceBroker\T3api\Event\RecordUpdatedEvent;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Event\Persistence\EntityAddedToPersistenceEvent;
use TYPO3\CMS\Extbase\Event\Persistence\EntityRemovedFromPersistenceEvent;
use TYPO3\CMS\Extbase\Event\Persistence\EntityUpdatedInPersistenceEvent;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;

/**
 * Listens to Extbase's granular per-operation persistence events rather than the coarser
 * `EntityPersistedEvent`, which fires for every object visited while walking the persistence
 * graph - even ones with nothing actually written to the database. These three events fire
 * only after a real INSERT/UPDATE/DELETE.
 */
final class PersistenceEventListener
{
    public function __construct(
        private readonly DataMapFactory $dataMapFactory,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function entityAdded(EntityAddedToPersistenceEvent $event): void
    {
        $this->dispatchRecordUpdated($event->getObject(), changesCollectionMembership: true);
    }

    public function entityUpdated(EntityUpdatedInPersistenceEvent $event): void
    {
        $this->dispatchRecordUpdated($event->getObject(), changesCollectionMembership: false);
    }

    public function entityRemoved(EntityRemovedFromPersistenceEvent $event): void
    {
        $object = $event->getObject();
        $this->eventDispatcher->dispatch(
            new RecordDeletedEvent((int)$object->getUid(), $this->getTableName($object))
        );
    }

    private function dispatchRecordUpdated(DomainObjectInterface $object, bool $changesCollectionMembership): void
    {
        $this->eventDispatcher->dispatch(
            new RecordUpdatedEvent((int)$object->getUid(), $this->getTableName($object), $changesCollectionMembership)
        );
    }

    private function getTableName(object $object): string
    {
        return $this->dataMapFactory->buildDataMap(get_class($object))->getTableName();
    }
}
