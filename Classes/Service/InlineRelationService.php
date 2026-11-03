<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\PropertyMetadata;
use SourceBroker\T3api\Exception\BadRequestException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap\Relation;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;

/**
 * Records of relations stored in the related table (TCA `inline` with `foreign_field`, TCA `file`) keep the parent
 * record in their own row and belong to this parent only. An existing record of such relation can be assigned
 * (or updated by cascade persistence) only by the parent property it already belongs to. New records are created
 * without `uid`.
 */
class InlineRelationService
{
    public function __construct(
        private readonly DataMapFactory $dataMapFactory,
        private readonly ConnectionPool $connectionPool
    ) {}

    /**
     * Checks an existing record given by UID for the property which is currently deserialized - the parent is the
     * object deserialized by the visitor, the property is the top of the metadata stack.
     *
     * @throws BadRequestException
     */
    public function assertAssignable(AbstractDomainObject $record, DeserializationContext $context, int $code): void
    {
        $parent = $this->getContextParent($context);
        $metadataStack = $context->getMetadataStack();
        $propertyMetadata = $metadataStack->isEmpty() ? null : $metadataStack->top();

        if (
            $parent !== null
            && $propertyMetadata instanceof PropertyMetadata
            && $this->checkAssignable($parent, $propertyMetadata->name, $record) === false
        ) {
            throw new BadRequestException(
                'relation_not_assignable',
                [(string)$record->getUid(), implode('.', $context->getCurrentPath())],
                $code
            );
        }
    }

    /**
     * The object which is currently deserialized - the parent of the relation which is deserialized.
     */
    public function getContextParent(DeserializationContext $context): ?AbstractDomainObject
    {
        $visitor = $context->getVisitor();
        $parent = method_exists($visitor, 'getCurrentObject') ? $visitor->getCurrentObject() : null;

        return $parent instanceof AbstractDomainObject ? $parent : null;
    }

    /**
     * Whether `$record` already belongs to property `$propertyName` of `$parent` - checked in the database, as the
     * property of `$parent` may be reset before deserialization (PUT). Null when the relation is not stored in the
     * related table.
     */
    public function checkAssignable(AbstractDomainObject $parent, string $propertyName, AbstractDomainObject $record): ?bool
    {
        $parentDataMap = $this->dataMapFactory->buildDataMap(get_class($parent));
        $columnMap = $this->getRelatedTableColumnMap($parentDataMap, $propertyName);

        return $columnMap === null
            ? null
            : $this->belongsToParent($parent, $parentDataMap->getTableName(), $columnMap, $record);
    }

    public function isStoredInRelatedTable(AbstractDomainObject $parent, string $propertyName): bool
    {
        return $this->getRelatedTableColumnMap(
            $this->dataMapFactory->buildDataMap(get_class($parent)),
            $propertyName
        ) !== null;
    }

    public function isAssignable(AbstractDomainObject $parent, string $propertyName, AbstractDomainObject $record): bool
    {
        return $this->checkAssignable($parent, $propertyName, $record) === true;
    }

    private function getRelatedTableColumnMap(DataMap $parentDataMap, string $propertyName): ?ColumnMap
    {
        $columnMap = $parentDataMap->getColumnMap($propertyName);

        return $columnMap !== null
            && in_array($columnMap->getTypeOfRelation(), [Relation::HAS_ONE, Relation::HAS_MANY], true)
            && (string)$columnMap->getParentKeyFieldName() !== ''
            && (string)$columnMap->getChildTableName() !== ''
                ? $columnMap
                : null;
    }

    private function belongsToParent(
        AbstractDomainObject $parent,
        string $parentTable,
        ColumnMap $columnMap,
        AbstractDomainObject $record
    ): bool {
        $parentKeyField = (string)$columnMap->getParentKeyFieldName();
        $parentTableField = (string)$columnMap->getParentTableFieldName();
        $matchFields = $columnMap->getRelationTableMatchFields() ?? [];

        // The row Extbase updates when persisting the record: the translation for a translated record.
        $recordUid = (int)($record->_getProperty(AbstractDomainObject::PROPERTY_LOCALIZED_UID) ?: $record->getUid());
        $row = $this->fetchParentFields(
            (string)$columnMap->getChildTableName(),
            $recordUid,
            array_filter([$parentKeyField, $parentTableField, ...array_keys($matchFields)])
        );
        if ($row === null) {
            return false;
        }

        // The parent key Extbase writes: for records of a collection the localized UID of a translated parent, so
        // a record stored with another value would be moved. A record of a single relation keeps its parent key,
        // which is the default language UID of the parent when the record was inserted.
        $localizedParentUid = (int)($parent->_getProperty(AbstractDomainObject::PROPERTY_LOCALIZED_UID) ?: $parent->getUid());
        $parentUids = $columnMap->getTypeOfRelation() === Relation::HAS_MANY
            ? [$localizedParentUid]
            : [$localizedParentUid, (int)$parent->getUid()];
        $parentUids = array_filter($parentUids);
        if (!in_array((int)$row[$parentKeyField], $parentUids, true)) {
            return false;
        }

        if ($parentTableField !== '' && (string)$row[$parentTableField] !== $parentTable) {
            return false;
        }

        foreach ($matchFields as $field => $value) {
            if ((string)$row[$field] !== (string)$value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[] $fields
     * @return array<string, mixed>|null
     */
    private function fetchParentFields(string $table, int $uid, array $fields): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        // Hidden records and records not visible in the current context still belong to their parent.
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

        $row = $queryBuilder
            ->select(...$fields)
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return $row === false ? null : $row;
    }
}
