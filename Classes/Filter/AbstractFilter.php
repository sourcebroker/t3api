<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Filter;

use SourceBroker\Restify\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class AbstractFilter
 */
abstract class AbstractFilter implements SingletonInterface
{
    /**
     * @param string $property
     * @param mixed $values
     * @param QueryInterface $query
     * @param ApiFilter $apiFilter
     *
     * @return mixed
     */
    abstract public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ConstraintInterface;
}
