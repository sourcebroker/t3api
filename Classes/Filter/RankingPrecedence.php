<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

/**
 * Where the ranking of an AbstractOrderedUidsFilter goes relative to the orderings requested
 * through OrderFilter (`order[...]`). Configured per declaration with the `rankingPrecedence`
 * argument of @ApiFilter.
 */
enum RankingPrecedence: string
{
    /**
     * The ranking is the primary ordering; `order[...]` only breaks its ties.
     */
    case BeforeOrderFilter = 'beforeOrderFilter';

    /**
     * `order[...]` is the primary ordering; the ranking breaks its ties.
     */
    case AfterOrderFilter = 'afterOrderFilter';
}
