<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Hook;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Event\RecordDeletedEvent;
use SourceBroker\T3api\Event\RecordUpdatedEvent;
use SourceBroker\T3api\Hook\ResponseCacheDataHandlerHook;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ResponseCacheDataHandlerHookTest extends UnitTestCase
{
    private const CTRL_WITH_CORE_ENABLE_COLUMNS = [
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'sortby' => 'sorting',
    ];

    private const CTRL_WITH_CUSTOM_ENABLE_COLUMN = [
        'enablecolumns' => [
            'disabled' => 'hidden',
            'public' => [
                'ownerField' => 'author',
                'fieldPermissions' => ['editor' => true],
            ],
        ],
    ];

    protected function tearDown(): void
    {
        unset($GLOBALS['TCA']['tx_foo']);
        parent::tearDown();
    }

    public static function updatedFieldsDataProvider(): array
    {
        return [
            'plain field' => [self::CTRL_WITH_CORE_ENABLE_COLUMNS, ['title' => 'x'], false],
            'disabled column' => [self::CTRL_WITH_CORE_ENABLE_COLUMNS, ['hidden' => 1], true],
            'starttime column' => [self::CTRL_WITH_CORE_ENABLE_COLUMNS, ['starttime' => 1_700_000_000], true],
            'pid' => [self::CTRL_WITH_CORE_ENABLE_COLUMNS, ['pid' => 42], true],
            'manual sorting field' => [self::CTRL_WITH_CORE_ENABLE_COLUMNS, ['sorting' => 256], true],
            'plain field of table with array enable column definition does not fail' => [
                self::CTRL_WITH_CUSTOM_ENABLE_COLUMN,
                ['title' => 'x'],
                false,
            ],
            'core enable column next to custom one' => [self::CTRL_WITH_CUSTOM_ENABLE_COLUMN, ['hidden' => 1], true],
            'name of custom restriction' => [self::CTRL_WITH_CUSTOM_ENABLE_COLUMN, ['public' => 1], true],
            'value in custom restriction definition' => [self::CTRL_WITH_CUSTOM_ENABLE_COLUMN, ['author' => 7], true],
            'nested key in custom restriction definition' => [
                self::CTRL_WITH_CUSTOM_ENABLE_COLUMN,
                ['editor' => 3],
                true,
            ],
        ];
    }

    #[DataProvider('updatedFieldsDataProvider')]
    #[Test]
    public function flagsMembershipChangeOnUpdateOnlyForVisibilityOrPlacementFields(
        array $ctrl,
        array $fields,
        bool $expectedMembershipChange
    ): void {
        $GLOBALS['TCA']['tx_foo']['ctrl'] = $ctrl;
        $eventDispatcher = $this->createEventDispatcherExpectingSingleEvent(
            static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 5
                && $event->getTable() === 'tx_foo'
                && $event->changesCollectionMembership() === $expectedMembershipChange
        );

        (new ResponseCacheDataHandlerHook($eventDispatcher))->processDatamap_afterDatabaseOperations(
            'update',
            'tx_foo',
            5,
            $fields,
            self::createStub(DataHandler::class)
        );
    }

    #[Test]
    public function resolvesNewRecordPlaceholderToRealUidAndFlagsMembershipChange(): void
    {
        $eventDispatcher = $this->createEventDispatcherExpectingSingleEvent(
            static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 42
                && $event->changesCollectionMembership() === true
        );
        $dataHandler = self::createStub(DataHandler::class);
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
        $eventDispatcher = $this->createEventDispatcherExpectingSingleEvent(
            static fn(RecordDeletedEvent $event): bool => $event->getUid() === 9 && $event->getTable() === 'tx_foo'
        );

        (new ResponseCacheDataHandlerHook($eventDispatcher))->processCmdmap_preProcess(
            'delete',
            'tx_foo',
            9,
            '',
            self::createStub(DataHandler::class)
        );
    }

    public static function membershipChangingCommandDataProvider(): array
    {
        return [
            'move' => ['move'],
            'undelete' => ['undelete'],
        ];
    }

    #[DataProvider('membershipChangingCommandDataProvider')]
    #[Test]
    public function dispatchesRecordUpdatedEventWithMembershipChangeOnCommand(string $command): void
    {
        $eventDispatcher = $this->createEventDispatcherExpectingSingleEvent(
            static fn(RecordUpdatedEvent $event): bool => $event->getUid() === 9
                && $event->getTable() === 'tx_foo'
                && $event->changesCollectionMembership() === true
        );

        (new ResponseCacheDataHandlerHook($eventDispatcher))->processCmdmap_preProcess(
            $command,
            'tx_foo',
            9,
            '',
            self::createStub(DataHandler::class)
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
            self::createStub(DataHandler::class)
        );
    }

    private function createEventDispatcherExpectingSingleEvent(\Closure $isExpectedEvent): EventDispatcherInterface
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch')->with(self::callback($isExpectedEvent));

        return $eventDispatcher;
    }
}
