<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Filter\AbstractOrderedUidsFilter;

/**
 * Concrete AbstractOrderedUidsFilter implementation for functional tests: it "resolves" the
 * ordered UID list directly from the request value (`?fixedOrder=3,1,2`), standing in for an
 * external search engine, so the expected order is fully controlled by the test. The special
 * value `none` simulates a lookup that was performed but matched nothing.
 */
class FixedOrderFilter extends AbstractOrderedUidsFilter
{
    protected function resolveOrderedUids($values, ApiFilter $apiFilter): ?array
    {
        $value = trim((string)(is_array($values) ? reset($values) : $values));

        if ($value === '') {
            return null;
        }

        if ($value === 'none') {
            return [];
        }

        return array_map('intval', explode(',', $value));
    }
}
