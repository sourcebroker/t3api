<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Command;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Command\InvalidateExpiredResponseCacheCommand;
use TYPO3\CMS\Core\Cache\Frontend\NullFrontend;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Registry;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class InvalidateExpiredResponseCacheCommandTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/t3api'];

    #[Test]
    public function detectsStarttimeChangeWithinExplicitWindow(): void
    {
        $now = time();
        $this->getConnectionPool()->getConnectionForTable('tt_content')->insert('tt_content', [
            'pid' => 1,
            'starttime' => $now - 60,
        ]);

        $command = $this->createCommand();

        self::assertTrue($this->callHasTimeBasedVisibilityChanged($command, 'tt_content', 0, $now));
    }

    #[Test]
    public function returnsFalseForWindowAfterTheChange(): void
    {
        $now = time();
        $this->getConnectionPool()->getConnectionForTable('tt_content')->insert('tt_content', [
            'pid' => 1,
            'starttime' => $now - 60,
        ]);

        $command = $this->createCommand();

        self::assertFalse($this->callHasTimeBasedVisibilityChanged($command, 'tt_content', $now, $now + 120));
    }

    #[Test]
    public function detectsEndtimeChangeWithinExplicitWindow(): void
    {
        $now = time();
        $this->getConnectionPool()->getConnectionForTable('tt_content')->insert('tt_content', [
            'pid' => 1,
            'endtime' => $now - 60,
        ]);

        $command = $this->createCommand();

        self::assertTrue($this->callHasTimeBasedVisibilityChanged($command, 'tt_content', 0, $now));
    }

    #[Test]
    public function returnsFalseForTablesWithoutEnableFields(): void
    {
        self::assertFalse($this->callHasTimeBasedVisibilityChanged($this->createCommand(), 'be_groups', 0, time()));
    }

    /**
     * Documents the silent-skip path a table without any TCA entry falls through:
     * `getStartTimeAndEndTimeFields()` resolves both enable-fields to null via `??` and
     * `hasTimeBasedVisibilityChanged()` returns false before ever building a query. Using a
     * table name absent from the test database schema too proves this - if a query were
     * attempted, it would fail with a DBAL exception instead of this assertion.
     */
    #[Test]
    public function returnsFalseWithoutTouchingDatabaseForTableWithoutAnyTcaEntry(): void
    {
        self::assertFalse(
            $this->callHasTimeBasedVisibilityChanged(
                $this->createCommand(),
                'tx_t3api_test_table_without_tca',
                0,
                time()
            )
        );
    }

    /**
     * The scheduler command scans every time-restricted TCA table, not just tables backing an
     * `@ApiResource` - a cached response can embed related entities from other tables (e.g. an
     * author nested in a book response), so this table must be picked up purely because it
     * declares a `starttime` enablecolumn, regardless of any `@ApiResource` annotation.
     */
    #[Test]
    public function getTimeRestrictedTablesIncludesTableWithStarttimeAndNoApiResource(): void
    {
        $table = 'tx_t3api_test_table_without_api_resource';
        $GLOBALS['TCA'][$table] = [
            'ctrl' => [
                'enablecolumns' => [
                    'starttime' => 'starttime',
                ],
            ],
        ];

        try {
            $tables = $this->callGetTimeRestrictedTables($this->createCommand());
        } finally {
            unset($GLOBALS['TCA'][$table]);
        }

        self::assertContains($table, $tables);
    }

    private function createCommand(): InvalidateExpiredResponseCacheCommand
    {
        return new InvalidateExpiredResponseCacheCommand(
            $this->get(ConnectionPool::class),
            $this->get(Registry::class),
            new NullFrontend('t3api_response')
        );
    }

    private function callHasTimeBasedVisibilityChanged(
        InvalidateExpiredResponseCacheCommand $command,
        string $table,
        int $lastExecutionTimestamp,
        int $executionTimestamp
    ): bool {
        $method = (new \ReflectionClass($command))->getMethod('hasTimeBasedVisibilityChanged');
        $method->setAccessible(true);

        return $method->invoke($command, $table, $lastExecutionTimestamp, $executionTimestamp);
    }

    /**
     * @return string[]
     */
    private function callGetTimeRestrictedTables(InvalidateExpiredResponseCacheCommand $command): array
    {
        $method = (new \ReflectionClass($command))->getMethod('getTimeRestrictedTables');
        $method->setAccessible(true);

        return $method->invoke($command);
    }
}
