<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Response;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
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

        $paginatedQuery = clone $this->query;

        // When the query is backed by a raw Doctrine QueryBuilder statement (e.g. a custom
        // filter or operation handler that needs an ORDER BY the Extbase QOM cannot express,
        // such as ORDER BY FIELD(uid, ...) for a relevance ranking), the shallow clone above
        // still shares the very same QueryBuilder instance with $this->query. Extbase applies
        // this (paginated) query's limit/offset onto that shared builder at execution time,
        // and it then leaks into the separate, unpaginated count() query used by
        // getTotalItems() — turning its "SELECT COUNT(*) ... LIMIT n OFFSET m" into an empty
        // result, because the single count row is skipped by the offset. Give the paginated
        // query its own clone of the builder so the limit/offset stay isolated to the members
        // query and never reach the count query. No-op for normal QOM queries (no statement).
        if ($paginatedQuery instanceof Query) {
            $statement = $paginatedQuery->getStatement();
            if ($statement !== null && $statement->getStatement() instanceof QueryBuilder) {
                $paginatedQuery->statement(clone $statement->getStatement(), $statement->getBoundVariables());
            }
        }

        return $paginatedQuery
            ->setLimit($pagination->getNumberOfItemsPerPage())
            ->setOffset($pagination->getOffset());
    }
}
