<?php
declare(strict_types=1);

namespace SourceBroker\T3Api\Filter;

use SourceBroker\T3Api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use InvalidArgumentException;

/**
 * Class RangeFilter
 */
class RangeFilter extends AbstractFilter
{
    protected const PARAMETER_BETWEEN = 'between';
    protected const PARAMETER_GREATER_THAN = 'gt';
    protected const PARAMETER_GREATER_THAN_OR_EQUAL = 'gte';
    protected const PARAMETER_LESS_THAN = 'lt';
    protected const PARAMETER_LESS_THAN_OR_EQUAL = 'lte';

    /**
     * @inheritDoc
     * @throws InvalidQueryException
     * @throws InvalidArgumentException
     */
    public function filterProperty(
        $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $constraints = [];
        foreach ((array)$values as $operator => $value) {
            $constraint = $this->getConstraintForSingleItem($property, $operator, $value, $query);

            if ($constraint) {
                $constraints[] = $constraint;
            }
        }

        if (empty($constraints)) {
            return null;
        }

        return $query->logicalOr(...$constraints);
    }

    /**
     * @param string $property
     * @param string $operator
     * @param $value
     * @param QueryInterface $query
     *
     * @return ConstraintInterface|null
     * @throws InvalidQueryException
     */
    protected function getConstraintForSingleItem(
        string $property,
        string $operator,
        $value,
        QueryInterface $query
    ): ?ConstraintInterface {
        switch ($operator) {
            case self::PARAMETER_BETWEEN:
                list($valueMin, $valueMax) = explode('..', $value);

                return $query->logicalAnd([
                    $query->greaterThanOrEqual($property, $valueMin),
                    $query->lessThanOrEqual($property, $valueMax),
                ]);
            case self::PARAMETER_GREATER_THAN:
                return $query->greaterThan($property, $value);
            case self::PARAMETER_GREATER_THAN_OR_EQUAL:
                return $query->greaterThanOrEqual($property, $value);
            case self::PARAMETER_LESS_THAN:
                return $query->lessThan($property, $value);
            case self::PARAMETER_LESS_THAN_OR_EQUAL:
                return $query->lessThanOrEqual($property, $value);
            default:
                throw new InvalidArgumentException(
                    sprintf('Unkown operator of range filter %s', $operator),
                    1560929019063
                );
        }
    }
}
