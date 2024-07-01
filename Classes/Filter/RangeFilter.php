<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class RangeFilter extends AbstractFilter implements OpenApiSupportingFilterInterface
{
    /**
     * @var string
     */
    protected const PARAMETER_BETWEEN = 'between';

    /**
     * @var string
     */
    protected const PARAMETER_GREATER_THAN = 'gt';

    /**
     * @var string
     */
    protected const PARAMETER_GREATER_THAN_OR_EQUAL = 'gte';

    /**
     * @var string
     */
    protected const PARAMETER_LESS_THAN = 'lt';

    /**
     * @var string
     */
    protected const PARAMETER_LESS_THAN_OR_EQUAL = 'lte';

    /**
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
     * @throws \InvalidArgumentException
     */
    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $constraints = [];
        foreach ((array)$values as $operator => $value) {
            $constraint = $this->getConstraintForSingleItem($property, $operator, $value, $query, $apiFilter);

            if ($constraint instanceof ConstraintInterface) {
                $constraints[] = $constraint;
            }
        }

        if ($constraints === []) {
            return null;
        }

        return $query->logicalOr(...$constraints);
    }

    /**
     * @param mixed $value
     *
     * @throws InvalidQueryException
     * @throws \Exception
     */
    protected function getConstraintForSingleItem(
        string $property,
        string $operator,
        $value,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        switch ($operator) {
            case self::PARAMETER_BETWEEN:
                [$valueMin, $valueMax] = explode('..', $value);

                return $query->logicalAnd(...[
                    $query->greaterThanOrEqual($property, $this->getValue($valueMin, $apiFilter)),
                    $query->lessThanOrEqual($property, $this->getValue($valueMax, $apiFilter)),
                ]);
            case self::PARAMETER_GREATER_THAN:
                return $query->greaterThan($property, $this->getValue($value, $apiFilter));
            case self::PARAMETER_GREATER_THAN_OR_EQUAL:
                return $query->greaterThanOrEqual($property, $this->getValue($value, $apiFilter));
            case self::PARAMETER_LESS_THAN:
                return $query->lessThan($property, $this->getValue($value, $apiFilter));
            case self::PARAMETER_LESS_THAN_OR_EQUAL:
                return $query->lessThanOrEqual($property, $this->getValue($value, $apiFilter));
            default:
                throw new \InvalidArgumentException(
                    sprintf('Unknown operator of range filter `%s`', $operator),
                    1560929019063
                );
        }
    }

    /**
     * @param $value
     * @return \DateTime|int
     * @throws \Exception
     */
    protected function getValue($value, ApiFilter $apiFilter)
    {
        switch (strtolower($apiFilter->getStrategy()->getName())) {
            case 'datetime':
                return new \DateTime($value);
            case 'int':
            case 'integer':
            case 'number':
            default:
                return (int)$value;
        }
    }
}
