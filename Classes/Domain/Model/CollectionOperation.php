<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Model;

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

    /**
     * @return string[]
     */
    public function getContextGroups(): array
    {
        return !empty($this->normalizationContext['groups'])
            ? array_merge($this->normalizationContext['groups'], ['__hydra_collection_response'])
            : [];
    }
}
