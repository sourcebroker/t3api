<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class BooleanFilter
 */
class BooleanFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filterProperty($property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
    {
        return $query->equals($property, !!((array)$values)[0]);
    }
}
