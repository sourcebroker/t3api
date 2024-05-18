<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Response;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

abstract class AbstractCollectionResponse
{
    protected CollectionOperation $operation;

    protected QueryInterface $query;

    protected Request $request;

    protected ?array $membersCache = null;

    protected ?int $totalItemsCache = null;

    abstract public static function getOpenApiSchema(string $membersReference): Schema;

    public function __construct(CollectionOperation $operation, Request $request, QueryInterface $query)
    {
        $this->operation = $operation;
        $this->request = $request;
        $this->query = $query;
    }

    public function getMembers(): array
    {
        if ($this->membersCache === null) {
            $this->membersCache = $this->applyPagination()->execute()->toArray();
        }

        return $this->membersCache;
    }

    public function getTotalItems(): int
    {
        if ($this->totalItemsCache === null) {
            $this->totalItemsCache = $this->query->execute()->count();
        }

        return $this->totalItemsCache;
    }

    protected function applyPagination(): QueryInterface
    {
        $pagination = $this->operation->getPagination()->setParametersFromRequest($this->request);

        if (!$pagination->isEnabled()) {
            return $this->query;
        }

        return (clone $this->query)
            ->setLimit($pagination->getNumberOfItemsPerPage())
            ->setOffset($pagination->getOffset());
    }
}
