<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Response;

use JMS\Serializer\Annotation as Serializer;
use SourceBroker\Restify\Domain\Model\CollectionOperation;
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
        return $this->applyPagination()->execute()->toArray();
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->query->execute()->count();
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
        return $query->setLimit($pagination->getItemsPerPageNumber())
            ->setOffset($pagination->getOffset());
    }
}
