<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Response;

use JMS\Serializer\Annotation as Serializer;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class AbstractCollectionResponse
 *
 * @Serializer\ExclusionPolicy("ALL")
 */
abstract class AbstractCollectionResponse
{
    /**
     * @var CollectionOperation
     */
    protected $operation;

    /**
     * @var QueryResultInterface
     */
    protected $query;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array|null
     */
    protected $membersCache = null;

    /**
     * @var int|null
     */
    protected $totalItemsCache = null;

    /**
     * CollectionResponse constructor.
     *
     * @param CollectionOperation $operation
     * @param Request $request
     * @param QueryInterface $query
     */
    public function __construct(CollectionOperation $operation, Request $request, QueryInterface $query)
    {
        $this->operation = $operation;
        $this->request = $request;
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function getMembers(): array
    {
        if ($this->membersCache === null) {
            $this->membersCache = $this->applyPagination()->execute()->toArray();
        }

        return $this->membersCache;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        if ($this->totalItemsCache === null) {
            $this->totalItemsCache = $this->query->execute()->count();
        }

        return $this->totalItemsCache;
    }

    /**
     * @return QueryInterface
     */
    protected function applyPagination(): QueryInterface
    {
        $pagination = $this->operation->getApiResource()->getPagination()->setParametersFromRequest($this->request);

        if (!$pagination->isEnabled()) {
            return $this->query;
        }

        return (clone $this->query)
            ->setLimit($pagination->getNumberOfItemsPerPage())
            ->setOffset($pagination->getOffset());
    }
}
