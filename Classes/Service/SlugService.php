<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;

/**
 * Processes TCA fields of type `slug` the same way DataHandler does: empty value is generated
 * from `generatorOptions`, non-empty value is sanitized, and then `eval` uniqueness rules
 * (`unique`, `uniqueInSite`, `uniqueInPid`) are applied.
 *
 * Has to be called after the object is persisted, so the full record (uid, pid, language) is
 * available for generation and uniqueness checks.
 */
class SlugService
{
    public function __construct(
        private readonly DataMapFactory $dataMapFactory,
        private readonly ConnectionPool $connectionPool
    ) {}

    public function processSlugs(AbstractDomainObject $object): void
    {
        $dataMap = $this->dataMapFactory->buildDataMap(get_class($object));
        $tableName = $dataMap->getTableName();
        $slugFields = $this->getSlugFields($tableName);

        if ($slugFields === []) {
            return;
        }

        $uid = (int)($object->_getProperty(AbstractDomainObject::PROPERTY_LOCALIZED_UID) ?? $object->getUid());
        $record = $this->getRecord($tableName, $uid);

        if ($record === null) {
            return;
        }

        $changedValues = [];
        foreach ($slugFields as $fieldName => $fieldConfig) {
            $value = (string)($record[$fieldName] ?? '');
            $processedValue = $this->processSlug($tableName, $fieldName, $fieldConfig, $value, $record);
            if ($processedValue !== $value) {
                $changedValues[$fieldName] = $processedValue;
                $record[$fieldName] = $processedValue;
            }
        }

        if ($changedValues === []) {
            return;
        }

        $this->connectionPool
            ->getConnectionForTable($tableName)
            ->update($tableName, $changedValues, ['uid' => $uid]);

        foreach (array_keys($object->_getProperties()) as $propertyName) {
            $columnName = $dataMap->getColumnMap($propertyName)?->getColumnName();
            if ($columnName !== null && array_key_exists($columnName, $changedValues)) {
                $object->_setProperty($propertyName, $changedValues[$columnName]);
                $object->_memorizeCleanState($propertyName);
            }
        }
    }

    /**
     * @see \TYPO3\CMS\Core\DataHandling\DataHandler::checkValueForSlug()
     */
    protected function processSlug(
        string $tableName,
        string $fieldName,
        array $fieldConfig,
        string $value,
        array $record
    ): string {
        $pid = (int)$record['pid'];
        $helper = GeneralUtility::makeInstance(SlugHelper::class, $tableName, $fieldName, $fieldConfig);
        $value = $value === '' ? $helper->generate($record, $pid) : $helper->sanitize($value);

        $evalCodes = GeneralUtility::trimExplode(',', (string)($fieldConfig['eval'] ?? ''), true);
        if ($evalCodes === []) {
            return $value;
        }

        $state = RecordStateFactory::forName($tableName)->fromArray($record, $pid, (int)$record['uid']);
        if (in_array('unique', $evalCodes, true)) {
            $value = $helper->buildSlugForUniqueInTable($value, $state);
        }
        if (in_array('uniqueInSite', $evalCodes, true)) {
            $value = $helper->buildSlugForUniqueInSite($value, $state);
        }
        if (in_array('uniqueInPid', $evalCodes, true)) {
            $value = $helper->buildSlugForUniqueInPid($value, $state);
        }

        return $value;
    }

    /**
     * @return array<string, array> Field configurations indexed by field name
     */
    protected function getSlugFields(string $tableName): array
    {
        $slugFields = [];
        foreach ($GLOBALS['TCA'][$tableName]['columns'] ?? [] as $fieldName => $column) {
            if (($column['config']['type'] ?? '') === 'slug') {
                $slugFields[$fieldName] = $column['config'];
            }
        }

        return $slugFields;
    }

    protected function getRecord(string $tableName, int $uid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll();
        $record = $queryBuilder
            ->select('*')
            ->from($tableName)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return $record === false ? null : $record;
    }
}
