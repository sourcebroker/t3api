<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * A filter that, in addition to (or instead of) contributing a constraint via
 * FilterInterface::filterProperty(), post-processes the whole query after every filter's
 * constraints have been combined and applied (see CommonRepository::findFiltered()).
 *
 * This is the seam for adjustments the QOM constraint model cannot express — most notably a
 * custom ORDER BY such as ORDER BY FIELD(uid, ...) for a relevance ranking, which needs the
 * query to already carry all constraints. Filters implementing this run in a second pass, in the
 * same order as filterProperty() - the sorted order of their parameter names, as Symfony's
 * Request::getQueryString() sorts the query parameters (see CommonRepository::findFiltered()).
 *
 * A modifier needing a Doctrine QueryBuilder should take the one shared by all modifiers from
 * AbstractFilter::getOrCreateStatementQueryBuilder() (see StatementQueryBuilderProvider for the
 * composition rules) instead of calling $query->statement(...) itself: Extbase executes only the
 * statement, so a later statement(...) call overwrites an earlier one (last one wins). For the same
 * reason, changes made through the QOM API in modifyQuery() are ignored once any modifier created
 * the statement - QOM changes belong to filterProperty().
 */
interface QueryModifierInterface
{
    public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void;
}
