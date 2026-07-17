<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Filter\AbstractFilter;
use SourceBroker\T3api\Filter\QueryModifierInterface;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Statement-based query modifier for functional tests contributing something other than an
 * ORDER BY: a WHERE condition the QOM constraint model cannot express (an SQL function applied
 * to a column) — `?maxTitleLength=10` keeps only records whose title is at most that long.
 *
 * Follows the composability rule from the QueryModifierInterface docs: when an earlier modifier
 * already rewrote the query into a QueryBuilder statement, the condition is appended to that
 * builder instead of reconverting the QOM.
 */
class MaxTitleLengthFilter extends AbstractFilter implements QueryModifierInterface
{
    private int $maxLength = 0;

    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $this->maxLength = (int)(is_array($values) ? reset($values) : $values);

        return null;
    }

    public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void
    {
        $maxLength = $this->maxLength;
        $this->maxLength = 0;

        if ($maxLength <= 0 || !$query instanceof Query) {
            return;
        }

        $statement = $query->getStatement();
        if ($statement !== null && $statement->getStatement() instanceof QueryBuilder) {
            $queryBuilder = $statement->getStatement();
            $queryBuilder->andWhere($this->buildTitleLengthCondition($queryBuilder, $query, $maxLength));

            return;
        }

        $queryBuilder = GeneralUtility::makeInstance(Typo3DbQueryParser::class)
            ->convertQueryToDoctrineQueryBuilder($query);
        $queryBuilder->andWhere($this->buildTitleLengthCondition($queryBuilder, $query, $maxLength));

        $query->statement($queryBuilder);
    }

    private function buildTitleLengthCondition(QueryBuilder $queryBuilder, Query $query, int $maxLength): string
    {
        return 'LENGTH(' . $queryBuilder->quoteIdentifier($this->getTableName($query) . '.title') . ') <= ' . $maxLength;
    }
}
