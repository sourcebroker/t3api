<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Filter;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use InvalidArgumentException;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class RangeFilter
 */
class RangeFilter extends AbstractFilter implements OpenApiSupportingFilterInterface
{
    protected const PARAMETER_BETWEEN = 'between';
    protected const PARAMETER_GREATER_THAN = 'gt';
    protected const PARAMETER_GREATER_THAN_OR_EQUAL = 'gte';
    protected const PARAMETER_LESS_THAN = 'lt';
    protected const PARAMETER_LESS_THAN_OR_EQUAL = 'lte';

    /**
     * @param ApiFilter $apiFilter
     *
     * @return Parameter[]
     */
    public static function getOpenApiParameters(ApiFilter $apiFilter): array
    {
        return [
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . self::PARAMETER_BETWEEN . ']')
                ->schema(Schema::string())
                ->description('Two numbers separated by `..` (e.g. `5..10`)'),
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . self::PARAMETER_GREATER_THAN . ']')
                ->schema(Schema::number()),
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . self::PARAMETER_GREATER_THAN_OR_EQUAL . ']')
                ->schema(Schema::number()),
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . self::PARAMETER_LESS_THAN . ']')
                ->schema(Schema::number()),
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . self::PARAMETER_LESS_THAN_OR_EQUAL . ']')
                ->schema(Schema::number()),
        ];
    }

    /**
     * @inheritDoc
     * @throws InvalidQueryException
     * @throws InvalidArgumentException
     */
    public function filterProperty(
        string $property,
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
     * @throws InvalidQueryException
     * @return ConstraintInterface|null
     */
    protected function getConstraintForSingleItem(
        string $property,
        string $operator,
        $value,
        QueryInterface $query
    ): ?ConstraintInterface {
        switch ($operator) {
            case self::PARAMETER_BETWEEN:
                [$valueMin, $valueMax] = explode('..', $value);

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
                    sprintf('Unknown operator of range filter `%s`', $operator),
                    1560929019063
                );
        }
    }
}
