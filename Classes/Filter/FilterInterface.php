<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

interface FilterInterface
{
    /**
     * Planned for the next major version (v6): a CollectionOperation parameter will be added here
     * (as in QueryModifierInterface::modifyQuery()), mirroring API Platform's filter contract
     * where apply() receives the Operation. It cannot happen within 5.x — it would break every
     * existing custom filter implementation.
     */
    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface;
}
