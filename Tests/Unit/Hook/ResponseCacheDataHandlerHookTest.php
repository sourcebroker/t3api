<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Hook;

use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Event\RecordDeletedEvent;
use SourceBroker\T3api\Event\RecordUpdatedEvent;
use SourceBroker\T3api\Hook\ResponseCacheDataHandlerHook;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ResponseCacheDataHandlerHookTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TCA']['tx_foo']);
        parent::tearDown();
    }

    #[Test]
    public function dispatchesRecordUpdatedEventWithoutMembershipChangeForExistingRecord(): void
    {
        $this->setTcaForTxFoo();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 5
                    && $event->getTable() === 'tx_foo'
                    && $event->changesCollectionMembership() === false
            ));

        $fields = ['title' => 'x'];
        (new ResponseCacheDataHandlerHook($eventDispatcher))->processDatamap_afterDatabaseOperations(
            'update',
            'tx_foo',
            5,
            $fields,
            $this->createMock(DataHandler::class)
        );
    }

    #[Test]
    public function dispatchesRecordUpdatedEventWithMembershipChangeWhenDisabledColumnIsTouched(): void
    {
        $this->setTcaForTxFoo();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->changesCollectionMembership() === true
            ));

        $fields = ['hidden' => 1];
        (new ResponseCacheDataHandlerHook($eventDispatcher))->processDatamap_afterDatabaseOperations(
            'update',
            'tx_foo',
            5,
            $fields,
            $this->createMock(DataHandler::class)
        );
    }

    #[Test]
    public function dispatchesRecordUpdatedEventWithMembershipChangeWhenStarttimeIsTouched(): void
    {
        $this->setTcaForTxFoo();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->changesCollectionMembership() === true
            ));

        $fields = ['starttime' => 1_700_000_000];
        (new ResponseCacheDataHandlerHook($eventDispatcher))->processDatamap_afterDatabaseOperations(
            'update',
            'tx_foo',
            5,
            $fields,
            $this->createMock(DataHandler::class)
        );
    }

    #[Test]
    public function dispatchesRecordUpdatedEventWithMembershipChangeWhenPidIsTouched(): void
    {
        $this->setTcaForTxFoo();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->changesCollectionMembership() === true
            ));

        $fields = ['pid' => 42];
        (new ResponseCacheDataHandlerHook($eventDispatcher))->processDatamap_afterDatabaseOperations(
            'update',
            'tx_foo',
            5,
            $fields,
            $this->createMock(DataHandler::class)
        );
    }

    #[Test]
    public function dispatchesRecordUpdatedEventWithMembershipChangeWhenSortingFieldIsTouched(): void
    {
        $this->setTcaForTxFoo();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->changesCollectionMembership() === true
            ));

        $fields = ['sorting' => 256];
        (new ResponseCacheDataHandlerHook($eventDispatcher))->processDatamap_afterDatabaseOperations(
            'update',
            'tx_foo',
            5,
            $fields,
            $this->createMock(DataHandler::class)
        );
    }

    #[Test]
    public function resolvesNewRecordPlaceholderToRealUidAndFlagsMembershipChange(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 42
                    && $event->changesCollectionMembership() === true
            ));

        $dataHandler = $this->createMock(DataHandler::class);
        $dataHandler->substNEWwithIDs = ['NEW123' => 42];

        $fields = [];
        (new ResponseCacheDataHandlerHook($eventDispatcher))->processDatamap_afterDatabaseOperations(
            'new',
            'tx_foo',
            'NEW123',
            $fields,
            $dataHandler
        );
    }

    #[Test]
    public function dispatchesRecordDeletedEventOnDeleteCommand(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordDeletedEvent $event): bool => $event->getUid() === 9 && $event->getTable() === 'tx_foo'
            ));

        (new ResponseCacheDataHandlerHook($eventDispatcher))->processCmdmap_preProcess(
            'delete',
            'tx_foo',
            9,
            '',
            $this->createMock(DataHandler::class)
        );
    }

    #[Test]
    public function dispatchesRecordUpdatedEventWithMembershipChangeOnMoveCommand(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 9
                    && $event->getTable() === 'tx_foo'
                    && $event->changesCollectionMembership() === true
            ));

        (new ResponseCacheDataHandlerHook($eventDispatcher))->processCmdmap_preProcess(
            'move',
            'tx_foo',
            9,
            '',
            $this->createMock(DataHandler::class)
        );
    }

    #[Test]
    public function dispatchesRecordUpdatedEventWithMembershipChangeOnUndeleteCommand(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 9
                    && $event->getTable() === 'tx_foo'
                    && $event->changesCollectionMembership() === true
            ));

        (new ResponseCacheDataHandlerHook($eventDispatcher))->processCmdmap_preProcess(
            'undelete',
            'tx_foo',
            9,
            '',
            $this->createMock(DataHandler::class)
        );
    }

    #[Test]
    public function ignoresCopyCommand(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        (new ResponseCacheDataHandlerHook($eventDispatcher))->processCmdmap_preProcess(
            'copy',
            'tx_foo',
            9,
            '',
            $this->createMock(DataHandler::class)
        );
    }

    private function setTcaForTxFoo(): void
    {
        $GLOBALS['TCA']['tx_foo']['ctrl'] = [
            'enablecolumns' => [
                'disabled' => 'hidden',
                'starttime' => 'starttime',
                'endtime' => 'endtime',
                'fe_group' => 'fe_group',
            ],
            'sortby' => 'sorting',
        ];
    }
}
