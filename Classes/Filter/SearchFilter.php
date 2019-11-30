<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Filter;

use Doctrine\DBAL\FetchMode;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class SearchFilter
 */
class SearchFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     * @throws InvalidQueryException
     * @throws UnexpectedTypeException
     */
    public function filterProperty(
        $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $values = (array)$values;

        switch ($apiFilter->getStrategy()) {
            case 'partial':
                return $query->logicalOr(
                    array_map(
                        static function ($value) use ($query, $property) {
                            return $query->like($property, '%' . $value . '%');
                        },
                        $values
                    )
                );
            case 'matchAgainst':
                $ids = $this->matchAgainstFindIds(
                    $property,
                    $values,
                    $query,
                    (bool)$apiFilter->getArgument('withQueryExpansion')
                );

                return $query->in('uid', $ids + [0]);
            case 'exact':
            default:
                return $query->in($property, $values);
        }
    }

    /**
     * @param string $property
     * @param array $values
     * @param QueryInterface $query
     * @param bool $queryExpansion
     *
     * @throws UnexpectedTypeException
     * @return array
     */
    protected function matchAgainstFindIds(
        string $property,
        array $values,
        QueryInterface $query,
        bool $queryExpansion = false
    ): array {
        $tableName = $this->getTableName($query);
        $conditions = [];
        $binds = [];
        $rootAlias = 'o';
        $queryBuilder = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);

        if ($this->isPropertyNested($property)) {
            $joinedProperty = $this->addJoinsForNestedProperty($property, $rootAlias, $query, $queryBuilder);
            [$tableAlias, $propertyName] = $joinedProperty;
        } else {
            $tableAlias = $rootAlias;
            $propertyName = $property;
        }

        foreach ($values as $i => $value) {
            $key = ':text_ma_' . ((int)$i);
            $conditions[] = sprintf(
                'MATCH(%s) AGAINST (%s IN NATURAL LANGUAGE MODE %s)',
                $queryBuilder->quoteIdentifier(
                    $tableAlias . '.' . $this->getObjectManager()->get(DataMapper::class)
                        ->convertPropertyNameToColumnName($propertyName)
                ),
                $key,
                $queryExpansion ? ' WITH QUERY EXPANSION ' : ''
            );
            $binds[$key] = $value;
        }

        return $queryBuilder
            ->select($rootAlias . '.uid')
            ->from($tableName, $rootAlias)
            ->andWhere($queryBuilder->expr()->orX(...$conditions))
            ->setParameters($binds)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);
    }
}
