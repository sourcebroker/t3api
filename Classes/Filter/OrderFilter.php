<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use InvalidArgumentException;

/**
 * Class OrderFilter
 */
class OrderFilter extends AbstractFilter
{
    /**
     * @var array
     */
    public static $defaultArguments = [
        'orderParameterName' => 'order',
    ];

    /**
     * @inheritDoc
     */
    public function filterProperty(
        $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        if (!isset($values[$property])) {
            return null;
        }

        $defaultDirection = $apiFilter->getStrategy();
        $direction = strtoupper($values[$property] ?: $defaultDirection);

        if (empty($direction)) {
            return null;
        }

        if (!in_array($direction, [QueryInterface::ORDER_ASCENDING, QueryInterface::ORDER_DESCENDING])) {
            throw new InvalidArgumentException(sprintf('Unknown order direction %s', $direction), 1560890654236);
        }

        $query->setOrderings(array_merge($query->getOrderings(), [$property => $direction]));

        return null;
    }
}
