<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Filter\AbstractFilter;
use SourceBroker\T3api\Filter\QueryModifierInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Statement-based query modifier for functional tests contributing something other than an
 * ORDER BY: a WHERE condition the QOM constraint model cannot express (an SQL function applied
 * to a column) — `?maxTitleLength=10` keeps only records whose title is at most that long.
 *
 * The state is kept per declaration, as the fixture resource declares this filter twice.
 */
class MaxTitleLengthFilter extends AbstractFilter implements QueryModifierInterface
{
    /**
     * @var array<int, int>
     */
    private array $maxLengthPerDeclaration = [];

    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $this->maxLengthPerDeclaration[spl_object_id($apiFilter)] = (int)(is_array($values) ? reset($values) : $values);

        return null;
    }

    public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void
    {
        $maxLength = $this->maxLengthPerDeclaration[spl_object_id($apiFilter)] ?? 0;
        unset($this->maxLengthPerDeclaration[spl_object_id($apiFilter)]);

        if ($maxLength <= 0 || !$query instanceof Query) {
            return;
        }

        $queryBuilder = $this->getOrCreateStatementQueryBuilder($query);
        $queryBuilder->andWhere(
            'LENGTH(' . $queryBuilder->quoteIdentifier($this->getTableName($query) . '.title') . ') <= ' . $maxLength
        );
    }
}
