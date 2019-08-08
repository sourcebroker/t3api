<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Response;

use JMS\Serializer\Annotation as Serializer;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class AbstractCollectionResponse
 */
abstract class AbstractCollectionResponse
{
    /**
     * @var CollectionOperation
     * @Serializer\Exclude()
     */
    protected $operation;

    /**
     * @var QueryResultInterface
     * @Serializer\Exclude()
     */
    protected $query;

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
     * @param QueryInterface $query
     */
    public function __construct(CollectionOperation $operation, QueryInterface $query)
    {
        $this->operation = $operation;
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
        $pagination = $this->operation->getApiResource()->getPagination();
        if (!$pagination->isEnabled()) {
            return $this->query;
        }

        $query = clone $this->query;
        return $query->setLimit($pagination->getNumberOfItemsPerPage())
            ->setOffset($pagination->getOffset());
    }
}
