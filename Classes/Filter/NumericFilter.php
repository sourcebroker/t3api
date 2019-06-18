<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Filter;

use SourceBroker\Restify\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class NumericFilter
 */
class NumericFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     * @throws InvalidQueryException
     */
    public function filterProperty(
        $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        return $query->in($property, array_map('intval', (array)$values));
    }
}
