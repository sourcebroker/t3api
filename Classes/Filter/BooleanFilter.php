<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Filter;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Utility\ParameterUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class BooleanFilter
 */
class BooleanFilter extends AbstractFilter implements OpenApiSupportingFilterInterface
{
    /**
     * @param ApiFilter $apiFilter
     *
     * @return Parameter[]
     */
    public static function getOpenApiParameters(ApiFilter $apiFilter): array
    {
        return [
            Parameter::create()
                ->name($apiFilter->getParameterName())
                ->schema(Schema::boolean()),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterProperty(string $property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
    {
        return $query->equals($property, ParameterUtility::toBoolean(((array)$values)[0]));
    }
}
