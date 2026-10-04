<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Domain\DateTimeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * Extbase sets creation and modification date (TCA `ctrl.crdate` and `ctrl.tstamp`) only in the database row
 * (@see \TYPO3\CMS\Extbase\Persistence\Generic\Backend::addCommonDateFieldsToRow()), so the persisted object
 * still holds old values (e.g. `null` for new object). This service copies stored values to the object, mapped
 * the same way as Extbase maps them when the object is loaded from the database, so the response is the same
 * as the response of subsequent GET.
 *
 * @see \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::thawProperties()
 */
class CommonDateFieldsService
{
    public function __construct(
        private readonly DataMapFactory $dataMapFactory,
        private readonly ReflectionService $reflectionService,
        private readonly ConnectionPool $connectionPool,
        private readonly Features $features
    ) {}

    public function applyToObject(AbstractDomainObject $object): void
    {
        $dataMap = $this->dataMapFactory->buildDataMap(get_class($object));
        $columnNames = array_filter([
            $dataMap->getCreationDateColumnName(),
            $dataMap->getModificationDateColumnName(),
        ]);

        if ($columnNames === []) {
            return;
        }

        // Iterate over class schema, not object values - uninitialized typed properties are not returned by
        // `_getProperties()`.
        $properties = [];
        foreach ($this->reflectionService->getClassSchema($object)->getDomainObjectProperties() as $property) {
            $columnMap = $dataMap->getColumnMap($property->getName());
            if ($columnMap !== null && in_array($columnMap->getColumnName(), $columnNames, true)) {
                $properties[] = [$property, $columnMap];
            }
        }

        if ($properties === []) {
            return;
        }

        $row = $this->getRow(
            $dataMap->getTableName(),
            (int)($object->_getProperty(AbstractDomainObject::PROPERTY_LOCALIZED_UID) ?? $object->getUid()),
            array_map(static fn(array $item): string => $item[1]->getColumnName(), $properties)
        );

        if ($row === null) {
            return;
        }

        foreach ($properties as [$property, $columnMap]) {
            $value = $row[$columnMap->getColumnName()] ?? null;
            if ($value === null) {
                continue;
            }

            $type = $property->getPrimaryType();
            $className = $type?->getClassName();
            if (in_array($type?->getBuiltinType(), ['int', 'integer'], true)) {
                $value = (int)$value;
            } elseif ($className !== null && is_subclass_of($className, \DateTimeInterface::class)) {
                $value = $this->mapDateTime($value, $columnMap, $className);
            } else {
                continue;
            }

            if ($value !== null || $property->isNullable()) {
                $object->_setProperty($property->getName(), $value);
                $object->_memorizeCleanState($property->getName());
            }
        }
    }

    /**
     * @param class-string<\DateTimeInterface> $targetType
     * @see \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::mapDateTime()
     */
    protected function mapDateTime(int|string $value, ColumnMap $columnMap, string $targetType): ?\DateTimeInterface
    {
        // Feature exists since TYPO3 13 (enabled by default since TYPO3 14)
        if ($this->features->isFeatureEnabled('extbase.consistentDateTimeHandling')) {
            $dateTime = DateTimeFactory::createFromDatabaseValueAndTCAConfig(
                $value,
                [
                    'type' => 'datetime',
                    'format' => $columnMap->getDateTimeFormat(),
                    'dbType' => $columnMap->getDateTimeStorageFormat(),
                    'nullable' => $columnMap->isNullable(),
                ]
            );

            return $dateTime === null ? null : match ($targetType) {
                \DateTimeImmutable::class => $dateTime,
                \DateTime::class => \DateTime::createFromImmutable($dateTime),
                default => GeneralUtility::makeInstance($targetType, $dateTime->format('Y-m-d H:i:s.v e')),
            };
        }

        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00' || $value === '00:00:00') {
            return null;
        }
        if (!in_array($columnMap->getDateTimeStorageFormat(), QueryHelper::getDateTimeTypes(), true)) {
            $value = date('c', (int)$value);
        }

        return GeneralUtility::makeInstance($targetType, $value);
    }

    /**
     * @param string[] $columnNames
     */
    protected function getRow(string $tableName, int $uid, array $columnNames): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll();
        $row = $queryBuilder
            ->select(...$columnNames)
            ->from($tableName)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return $row === false ? null : $row;
    }
}
