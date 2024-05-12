<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Selector;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class AbstractFilter
 */
abstract class AbstractFilter implements SingletonInterface, FilterInterface
{
    /**
     * @var array
     */
    public static $defaultArguments = [];

    protected function isPropertyNested(string $propertyName): bool
    {
        return str_contains($propertyName, '.');
    }

    /**
     * @throws UnexpectedTypeException
     */
    protected function addJoinsForNestedProperty(
        string $property,
        string $rootAlias,
        QueryInterface $query,
        QueryBuilder $queryBuilder
    ): array {
        $propertyParts = $this->splitPropertyParts($property);

        $parentClassName = $query->getType();

        $dataMapFactory = GeneralUtility::makeInstance(DataMapFactory::class);
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class, $query);

        $parentAlias = $rootAlias;

        foreach ($propertyParts['associations'] as $associationProperty) {
            $dataMap = $dataMapFactory->buildDataMap($parentClassName);
            $columnMap = $dataMap->getColumnMap($associationProperty);
            $associationType = $dataMapper->getType($parentClassName, $associationProperty);
            $alias = $this->addJoin($parentAlias, $columnMap, $queryBuilder);
            $parentClassName = $associationType;
            $parentAlias = $alias;
        }

        if (!isset($alias)) {
            throw new \InvalidArgumentException(
                sprintf('Cannot add joins for property "%s" - property is not nested.', $property)
            );
        }

        return [$alias, $propertyParts['field'], $propertyParts['associations']];
    }

    /**
     * @return array{associations: string[], field: string}
     */
    protected function splitPropertyParts(string $property): array
    {
        $parts = explode('.', $property);

        return [
            'associations' => array_slice($parts, 0, -1),
            'field' => end($parts),
        ];
    }

    protected function addJoin(string $parentAlias, ColumnMap $columnMap, QueryBuilder $queryBuilder): string
    {
        $alias = $this->getUniqueAlias();

        switch ($columnMap->getTypeOfRelation()) {
            case ColumnMap\Relation::HAS_ONE:
                if ($columnMap->getParentKeyFieldName()) {
                    $joinConditionExpression = $queryBuilder->expr()->eq(
                        $parentAlias . '.uid',
                        $queryBuilder->quoteIdentifier($alias . '.' . $columnMap->getParentKeyFieldName())
                    );
                } else {
                    $joinConditionExpression = $queryBuilder->expr()->eq(
                        $parentAlias . '.' . $columnMap->getColumnName(),
                        $queryBuilder->quoteIdentifier($alias . '.uid')
                    );
                }

                break;
            case ColumnMap\Relation::HAS_MANY:
                if ($columnMap->getParentKeyFieldName()) {
                    $joinConditionExpression = $queryBuilder->expr()->eq(
                        $parentAlias . '.uid',
                        $queryBuilder->quoteIdentifier($alias . '.' . $columnMap->getParentKeyFieldName())
                    );
                } else {
                    $joinConditionExpression = $queryBuilder->expr()->inSet(
                        $parentAlias . '.' . $columnMap->getColumnName(),
                        $queryBuilder->quoteIdentifier($alias . '.uid'),
                        true
                    );
                }

                break;
            case ColumnMap\Relation::HAS_AND_BELONGS_TO_MANY:
                $relationalAlias = $this->getUniqueAlias('_mm');
                $queryBuilder->leftJoin(
                    $parentAlias,
                    $columnMap->getRelationTableName(),
                    $relationalAlias,
                    $queryBuilder->expr()->eq(
                        $parentAlias . '.uid',
                        $queryBuilder->quoteIdentifier(
                            $relationalAlias . '.' . $columnMap->getParentKeyFieldName()
                        )
                    )
                );

                $joinConditionExpression = $queryBuilder->expr()->eq(
                    $relationalAlias . '.' . $columnMap->getChildKeyFieldName(),
                    $queryBuilder->quoteIdentifier($alias . '.uid')
                );
                break;
            case ColumnMap\Relation::BELONGS_TO_MANY:
            default:
                throw new \InvalidArgumentException('Could not determine relation', 1562191351170);
        }

        $queryBuilder->leftJoin(
            $parentAlias,
            $columnMap->getChildTableName(),
            $alias,
            $joinConditionExpression
        );

        return $alias;
    }

    protected function getUniqueAlias(string $suffix = ''): string
    {
        return uniqid('alias_', false) . $suffix;
    }

    /**
     * @throws \RuntimeException
     */
    protected function getTableName(QueryInterface $query): string
    {
        if (!$query instanceof Query) {
            throw new \RuntimeException(
                sprintf('Query needs to be instance of %s to read source', Query::class),
                1575123607933
            );
        }

        /** @var Selector $source */
        $source = $query->getSource();

        if (!$query->getSource() instanceof SelectorInterface) {
            throw new \RuntimeException('Query source does not implement SelectorInterface.', 1575123611889);
        }

        return $source->getSelectorName();
    }
}
