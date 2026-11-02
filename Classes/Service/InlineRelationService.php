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
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Records of relations stored in the related table (TCA `inline` with `foreign_field`, TCA `file`) keep the parent
 * record in their own row. Assigning such an existing record to a property moves it to the new parent, so it is
 * allowed only for records without parent and for records which already belong to the same parent property.
 */
class InlineRelationService
{
    public function __construct(
        private readonly DataMapper $dataMapper,
        private readonly ConnectionPool $connectionPool
    ) {}

    /**
     * Checks an existing record given by UID for the property which is currently deserialized - the parent is the
     * object deserialized by the visitor, the property is the top of the metadata stack.
     *
     * @throws BadRequestException
     */
    public function assertAssignable(AbstractDomainObject $record, DeserializationContext $context): void
    {
        $visitor = $context->getVisitor();
        $parent = method_exists($visitor, 'getCurrentObject') ? $visitor->getCurrentObject() : null;
        $metadataStack = $context->getMetadataStack();
        $propertyMetadata = $metadataStack->isEmpty() ? null : $metadataStack->top();

        if (
            $parent instanceof AbstractDomainObject
            && $propertyMetadata instanceof PropertyMetadata
            && !$this->isAssignable($parent, $propertyMetadata->name, $record)
        ) {
            throw new BadRequestException(
                'relation_not_assignable',
                [(string)$record->getUid(), implode('.', $context->getCurrentPath())],
                1793660001
            );
        }
    }

    public function isAssignable(AbstractDomainObject $parent, string $propertyName, AbstractDomainObject $record): bool
    {
        $columnMap = $this->dataMapper->getDataMap(get_class($parent))->getColumnMap($propertyName);
        if ($columnMap === null || !$this->isStoredInRelatedTable($columnMap)) {
            return true;
        }

        $parentKeyField = (string)$columnMap->getParentKeyFieldName();
        $parentTableField = (string)$columnMap->getParentTableFieldName();
        $matchFields = $columnMap->getRelationTableMatchFields() ?? [];

        $row = $this->fetchParentFields(
            (string)$columnMap->getChildTableName(),
            (int)$record->getUid(),
            array_filter([$parentKeyField, $parentTableField, ...array_keys($matchFields)])
        );
        if ($row === null) {
            return false;
        }

        if ((int)$row[$parentKeyField] === 0) {
            return true;
        }

        // Same value as written by Extbase persistence - a translated parent is stored with its localized UID.
        $parentUid = (int)($parent->_getProperty(AbstractDomainObject::PROPERTY_LOCALIZED_UID) ?: $parent->getUid());
        if ($parentUid === 0 || (int)$row[$parentKeyField] !== $parentUid) {
            return false;
        }

        if (
            $parentTableField !== ''
            && (string)$row[$parentTableField] !== $this->dataMapper->getDataMap(get_class($parent))->getTableName()
        ) {
            return false;
        }

        foreach ($matchFields as $field => $value) {
            if ((string)$row[$field] !== (string)$value) {
                return false;
            }
        }

        return true;
    }

    private function isStoredInRelatedTable(ColumnMap $columnMap): bool
    {
        return in_array($columnMap->getTypeOfRelation(), [Relation::HAS_ONE, Relation::HAS_MANY], true)
            && (string)$columnMap->getParentKeyFieldName() !== ''
            && (string)$columnMap->getChildTableName() !== '';
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
