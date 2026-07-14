<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\EventListener;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Event\RecordDeletedEvent;
use SourceBroker\T3api\Event\RecordUpdatedEvent;
use SourceBroker\T3api\EventListener\PersistenceEventListener;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Event\Persistence\EntityAddedToPersistenceEvent;
use TYPO3\CMS\Extbase\Event\Persistence\EntityRemovedFromPersistenceEvent;
use TYPO3\CMS\Extbase\Event\Persistence\EntityUpdatedInPersistenceEvent;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PersistenceEventListenerTest extends UnitTestCase
{
    #[Test]
    public function entityAddedDispatchesRecordUpdatedEventWithMembershipChange(): void
    {
        $object = $this->createDomainObjectMock(5);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 5
                    && $event->getTable() === 'tx_foo'
                    && $event->changesCollectionMembership() === true
            ));

        (new PersistenceEventListener($this->createDataMapFactory($object, 'tx_foo'), $eventDispatcher))
            ->entityAdded(new EntityAddedToPersistenceEvent($object));
    }

    #[Test]
    public function entityUpdatedDispatchesRecordUpdatedEventWithoutMembershipChange(): void
    {
        $object = $this->createDomainObjectMock(5);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 5
                    && $event->getTable() === 'tx_foo'
                    && $event->changesCollectionMembership() === false
            ));

        (new PersistenceEventListener($this->createDataMapFactory($object, 'tx_foo'), $eventDispatcher))
            ->entityUpdated(new EntityUpdatedInPersistenceEvent($object));
    }

    #[Test]
    public function entityRemovedDispatchesRecordDeletedEvent(): void
    {
        $object = $this->createDomainObjectMock(9);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordDeletedEvent $event): bool => $event->getUid() === 9 && $event->getTable() === 'tx_foo'
            ));

        (new PersistenceEventListener($this->createDataMapFactory($object, 'tx_foo'), $eventDispatcher))
            ->entityRemoved(new EntityRemovedFromPersistenceEvent($object));
    }

    private function createDomainObjectMock(int $uid): DomainObjectInterface&MockObject
    {
        $object = $this->createMock(DomainObjectInterface::class);
        $object->method('getUid')->willReturn($uid);

        return $object;
    }

    private function createDataMapFactory(DomainObjectInterface $object, string $tableName): DataMapFactory
    {
        $dataMapFactory = $this->createMock(DataMapFactory::class);
        $dataMapFactory->method('buildDataMap')
            ->with($object::class)
            ->willReturn(new DataMap($object::class, $tableName));

        return $dataMapFactory;
    }
}
