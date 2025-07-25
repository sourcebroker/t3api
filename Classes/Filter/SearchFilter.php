<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class SearchFilter extends AbstractFilter implements OpenApiSupportingFilterInterface
{
    /**
     * @return Parameter[]
     */
    public static function getOpenApiParameters(ApiFilter $apiFilter): array
    {
        return [
            Parameter::create()
                ->name($apiFilter->getParameterName())
                ->schema(Schema::string()),
        ];
    }

    /**
     * @inheritDoc
     * @throws InvalidQueryException
     * @throws UnexpectedTypeException
     */
    public function filterProperty(
        string $property,
               $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $values = (array)$values;

        switch ($apiFilter->getStrategy()->getName()) {
            case 'partial':
                return $query->logicalOr(
                    ...array_map(
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
                    $apiFilter
                );

                return $query->in('uid', $ids + [0]);
            case 'exact':
            default:
                return $query->in($property, $values);
        }
    }

    /**
     * @return int[]
     * @throws UnexpectedTypeException
     */
    protected function matchAgainstFindIds(
        string $property,
        array $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): array {
        $tableName = $this->getTableName($query);
        $conditions = [];
        $binds = [];
        $rootAlias = 'o';
        $queryExpansion = (bool)$apiFilter->getArgument('withQueryExpansion');
        $booleanQuery = (bool)$apiFilter->getArgument('withBooleanQuery');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);

        if ($this->isPropertyNested($property)) {
            [$tableAlias, $propertyName] = $this->addJoinsForNestedProperty(
                $property,
                $rootAlias,
                $query,
                $queryBuilder
            );
        } else {
            $tableAlias = $rootAlias;
            $propertyName = $property;
        }

        foreach ($values as $i => $value) {
            $key = ':text_ma_' . ((int)$i);

            if ($booleanQuery) {
                // Split the value into individual words and create OR conditions
                $words = explode(' ', trim($value));
                $booleanQuery = '';
                foreach ($words as $word) {
                    if (!empty(trim($word))) {
                        $booleanQuery .= trim($word) . '* ';
                    }
                }
                $booleanQuery = trim($booleanQuery);

                // use IN BOOLEAN MODE to search for partials of words
                $conditions[] = sprintf(
                    'MATCH(%s) AGAINST (%s IN BOOLEAN MODE)',
                    $queryBuilder->quoteIdentifier(
                        $tableAlias . '.' . GeneralUtility::makeInstance(DataMapper::class)
                            ->convertPropertyNameToColumnName($propertyName, $apiFilter->getFilterClass())
                    ),
                    $key
                );

                $binds[ltrim($key, ':')] = $booleanQuery;
            } else {
                // Original natural language mode query
                $conditions[] = sprintf(
                    'MATCH(%s) AGAINST (%s IN NATURAL LANGUAGE MODE %s)',
                    $queryBuilder->quoteIdentifier(
                        $tableAlias . '.' . GeneralUtility::makeInstance(DataMapper::class)
                            ->convertPropertyNameToColumnName($propertyName, $apiFilter->getFilterClass())
                    ),
                    $key,
                    $queryExpansion ? ' WITH QUERY EXPANSION ' : ''
                );

                $binds[ltrim($key, ':')] = $value;
            }
        }

        return $queryBuilder
            ->select($rootAlias . '.uid')
            ->from($tableName, $rootAlias)
            ->andWhere($queryBuilder->expr()->or(...$conditions))
            ->setParameters($binds)
            ->executeQuery()
            ->fetchFirstColumn();
    }
}
