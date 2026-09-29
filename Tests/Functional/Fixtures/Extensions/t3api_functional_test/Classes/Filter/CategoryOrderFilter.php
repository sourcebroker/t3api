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
 * Statement-based query modifier for functional tests applying an ordering WITH ties
 * (`ORDER BY category` — several products share a category), so that a subsequent ordered-UIDs
 * filter's FIELD() ranking observably acts as the tie-breaker within the category groups.
 */
class CategoryOrderFilter extends AbstractFilter implements QueryModifierInterface
{
    private bool $active = false;

    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $this->active = (bool)(is_array($values) ? reset($values) : $values);

        return null;
    }

    public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void
    {
        $active = $this->active;
        $this->active = false;

        if (!$active || !$query instanceof Query) {
            return;
        }

        $queryBuilder = $this->getOrCreateStatementQueryBuilder($query);
        $queryBuilder->addOrderBy($this->getTableName($query) . '.category');
    }
}
