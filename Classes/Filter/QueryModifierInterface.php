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
 * same (request-driven) order as filterProperty().
 *
 * Composability caveat: adjustments made through the QOM API compose across modifiers, but a
 * modifier that ends in $query->statement(...) effectively replaces the whole query — Extbase
 * executes the statement and ignores QOM-level changes made by later modifiers, and a later
 * statement(...) call overwrites an earlier one (last one wins). A statement-based modifier
 * should therefore check $query->getStatement() first and mutate the QueryBuilder already
 * present instead of reconverting, as AbstractOrderedUidsFilter does.
 */
interface QueryModifierInterface
{
    public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void;
}
