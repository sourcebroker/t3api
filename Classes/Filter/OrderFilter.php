<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class OrderFilter
 */
class OrderFilter extends AbstractFilter implements OpenApiSupportingFilterInterface
{
    /**
     * @var array
     */
    public static $defaultArguments = [
        'orderParameterName' => 'order',
    ];

    /**
     * @return Parameter[]
     */
    public static function getOpenApiParameters(ApiFilter $apiFilter): array
    {
        return [
            Parameter::create()
                ->name($apiFilter->getParameterName() . '[' . $apiFilter->getProperty() . ']')
                ->in(Parameter::IN_QUERY)
                ->schema(Schema::string()->enum('asc', 'desc')),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        if (!isset($values[$property])) {
            return null;
        }

        $defaultDirection = $apiFilter->getStrategy()->getName();
        $direction = strtoupper($values[$property] !== '' ? $values[$property] : $defaultDirection);

        if ($direction === '') {
            return null;
        }

        if (!in_array($direction, [QueryInterface::ORDER_ASCENDING, QueryInterface::ORDER_DESCENDING], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown order direction `%s`', $direction), 1560890654236);
        }

        $query->setOrderings(array_merge($query->getOrderings(), [$property => $direction]));

        return null;
    }
}
