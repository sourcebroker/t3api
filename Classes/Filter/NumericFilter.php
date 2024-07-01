<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class NumericFilter extends AbstractFilter implements OpenApiSupportingFilterInterface
{
    /**
     * @return Parameter[]
     */
    public static function getOpenApiParameters(ApiFilter $apiFilter): array
    {
        return [
            Parameter::create()
                ->name($apiFilter->getParameterName())
                ->schema(Schema::integer()),
        ];
    }

    /**
     * @inheritDoc
     * @throws InvalidQueryException
     */
    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        return $query->in($property, array_map('intval', (array)$values));
    }
}
