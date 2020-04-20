<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Model;

/**
 * Class CollectionOperation
 */
class CollectionOperation extends AbstractOperation
{
    /**
     * @var ApiFilter[]
     */
    protected $filters = [];

    /**
     * @param ApiFilter $apiFilter
     */
    public function addFilter(ApiFilter $apiFilter)
    {
        $this->filters[] = $apiFilter;
    }

    /**
     * @return ApiFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
