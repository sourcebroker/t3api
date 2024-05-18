<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

class CollectionOperation extends AbstractOperation
{
    /**
     * @var ApiFilter[]
     */
    protected array $filters = [];

    protected Pagination $pagination;

    public function __construct(string $key, ApiResource $apiResource, array $params)
    {
        parent::__construct($key, $apiResource, $params);
        $this->pagination = Pagination::create($params['attributes'] ?? [], $apiResource->getPagination());
    }

    public function addFilter(ApiFilter $apiFilter): void
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

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }
}
