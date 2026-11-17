<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Hook;

use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Event\RecordDeletedEvent;
use SourceBroker\T3api\Event\RecordUpdatedEvent;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\MathUtility;

class ResponseCacheDataHandlerHook
{
    public function __construct(protected readonly EventDispatcherInterface $eventDispatcher) {}

    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        int|string $uid,
        array &$fields,
        DataHandler $dataHandler
    ): void {
        if ($status === 'new' && !MathUtility::canBeInterpretedAsInteger($uid)) {
            if (!isset($dataHandler->substNEWwithIDs[$uid])) {
                return;
            }
            $uid = $dataHandler->substNEWwithIDs[$uid];
        }

        $this->eventDispatcher->dispatch(
            new RecordUpdatedEvent((int)$uid, $table, $this->affectsCollectionMembership($status, $table, $fields))
        );
    }

    /**
     * True for a new record (as before), and additionally for an update that touches a field
     * capable of moving the record in or out of a collection's result set: an enable-column
     * (e.g. `hidden`, `starttime`, `endtime`, `fe_group`), `pid`, or the table's manual sorting
     * field. A plain update to any other field cannot change collection membership.
     *
     * @param array<string, mixed> $fields
     */
    private function affectsCollectionMembership(string $status, string $table, array $fields): bool
    {
        if ($status === 'new') {
            return true;
        }

        return $this->touchesVisibilityOrPlacementField($table, $fields);
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function touchesVisibilityOrPlacementField(string $table, array $fields): bool
    {
        foreach ($this->getVisibilityOrPlacementFieldNames($table) as $fieldName) {
            if (array_key_exists($fieldName, $fields)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getVisibilityOrPlacementFieldNames(string $table): array
    {
        $ctrl = $GLOBALS['TCA'][$table]['ctrl'] ?? [];

        $fieldNames = $this->getEnableColumnFieldNameCandidates($ctrl['enablecolumns'] ?? []);
        $fieldNames[] = 'pid';

        if (!empty($ctrl['sortby'])) {
            $fieldNames[] = $ctrl['sortby'];
        }

        return $fieldNames;
    }

    /**
     * Core enable columns map a restriction to a field name. Extensions may register custom restrictions
     * with an array definition, e.g. `'public' => ['column' => 'public', 'ownerField' => 'author']`, whose
     * structure is unknown here. So the restriction name and every string key and value of such a definition
     * are treated as possible field names: a false hit only flushes more than needed, while a missed field
     * would leave stale collection responses.
     *
     * @param array<mixed> $enableColumns
     * @return string[]
     */
    private function getEnableColumnFieldNameCandidates(array $enableColumns): array
    {
        $candidates = [];
        foreach ($enableColumns as $restrictionName => $definition) {
            if (!is_array($definition)) {
                $candidates[] = $definition;
                continue;
            }
            $candidates[] = $restrictionName;
            array_push($candidates, ...$this->getKeysAndValuesRecursively($definition));
        }

        return array_values(array_filter(
            $candidates,
            static fn(mixed $candidate): bool => is_string($candidate) && $candidate !== ''
        ));
    }

    /**
     * @param array<mixed> $array
     * @return list<mixed>
     */
    private function getKeysAndValuesRecursively(array $array): array
    {
        $keysAndValues = [];
        foreach ($array as $key => $value) {
            $keysAndValues[] = $key;
            if (!is_array($value)) {
                $keysAndValues[] = $value;
                continue;
            }
            array_push($keysAndValues, ...$this->getKeysAndValuesRecursively($value));
        }

        return $keysAndValues;
    }

    public function processCmdmap_preProcess(
        string $command,
        string $table,
        int|string $uid,
        mixed $value,
        DataHandler $dataHandler
    ): void {
        if ($command === 'delete') {
            $this->eventDispatcher->dispatch(new RecordDeletedEvent((int)$uid, $table));

            return;
        }

        // A record moved or undeleted into a queried storage was in no cached entry before, so no
        // uid tag would match it - without this, only a plain create (handled via
        // processDatamap_afterDatabaseOperations()) would flush the affected collection responses.
        if ($command === 'move' || $command === 'undelete') {
            $this->eventDispatcher->dispatch(
                new RecordUpdatedEvent((int)$uid, $table, changesCollectionMembership: true)
            );
        }
    }
}
