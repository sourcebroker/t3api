<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Filter;

use SourceBroker\Restify\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class SearchFilter
 */
class SearchFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filterProperty($property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
    {
        $values = (array)$values;

        switch ($apiFilter->getStrategy()) {
            case 'partial':
                return $query->logicalOr(
                    array_map(
                        function ($value) use ($query, $property) {
                            return $query->like($property, '%' . $value . '%');
                        },
                        $values
                    )
                );
                break;
            case 'exact':
            default:
                return $query->in($property, $values);
        }
    }
}
