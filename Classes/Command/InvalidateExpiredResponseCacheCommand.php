<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Registry;

#[AsCommand(
    name: 't3api:cache:invalidate-expired',
    description: 'Invalidates the t3api response cache for tables where a start or end time passed since the last run.'
)]
class InvalidateExpiredResponseCacheCommand extends Command
{
    protected const REGISTRY_NAMESPACE = 't3api_response';

    public function __construct(
        protected readonly ConnectionPool $connectionPool,
        protected readonly Registry $registry,
        protected readonly FrontendInterface $cache
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $executionTimestamp = time();
        foreach ($this->getTimeRestrictedTables() as $table) {
            $registryKey = $this->getRegistryKeyForTable($table);
            $lastExecutionTimestamp = (int)$this->registry->get(self::REGISTRY_NAMESPACE, $registryKey);

            if ($this->hasTimeBasedVisibilityChanged($table, $lastExecutionTimestamp, $executionTimestamp)) {
                $output->writeln(sprintf('Flushing t3api response cache for table `%s`', $table));
                $this->cache->flushByTags([$table]);
            }

            // Persisted only after the visibility check (and the flush it may have triggered)
            // completed without throwing, so a DB error or crash does not silently lose this
            // invalidation window - the next run will simply re-check it.
            $this->registry->set(self::REGISTRY_NAMESPACE, $registryKey, $executionTimestamp);
        }

        return Command::SUCCESS;
    }

    /**
     * Scans every TCA table with a starttime/endtime enablecolumn, not just cacheable
     * resources: a cached response can embed related entities from other tables (e.g. an
     * author nested in a book response), so a table never checked here could never trigger
     * the flush that keeps those responses fresh. This broad scan stays safe because the
     * flush itself is tag-scoped - `flushByTags([$table])` only ever hits entries that
     * actually embedded a record of that table.
     *
     * @return string[]
     */
    protected function getTimeRestrictedTables(): array
    {
        $tables = [];
        foreach (array_keys($GLOBALS['TCA']) as $table) {
            if ($this->getStartTimeAndEndTimeFields($table) !== []) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    protected function hasTimeBasedVisibilityChanged(string $table, int $lastExecutionTimestamp, int $executionTimestamp): bool
    {
        $enableFields = $this->getStartTimeAndEndTimeFields($table);
        if ($enableFields === []) {
            return false;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        $constraints = [];
        foreach ($enableFields as $enableField) {
            $constraints[] = $queryBuilder->expr()->and(
                $queryBuilder->expr()->gt(
                    $enableField,
                    $queryBuilder->createNamedParameter($lastExecutionTimestamp, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->lte(
                    $enableField,
                    $queryBuilder->createNamedParameter($executionTimestamp, Connection::PARAM_INT)
                )
            );
        }

        return (int)$queryBuilder
            ->count('uid')
            ->from($table)
            ->where($queryBuilder->expr()->or(...$constraints))
            ->executeQuery()
            ->fetchOne() > 0;
    }

    /**
     * @return string[]
     */
    protected function getStartTimeAndEndTimeFields(string $table): array
    {
        return array_filter([
            'starttime' => $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['starttime'] ?? null,
            'endtime' => $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['endtime'] ?? null,
        ]);
    }

    protected function getRegistryKeyForTable(string $table): string
    {
        return sprintf('%s_lastExecution', $table);
    }
}
