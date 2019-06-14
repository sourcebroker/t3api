<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Hydra;

use JMS\Serializer\Annotation as Serializer;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class CollectionResponse
 */
class CollectionResponse
{

    /**
     * @var QueryResultInterface
     * @Serializer\Exclude()
     */
    protected $queryResult;

    /**
     * CollectionResponse constructor.
     *
     * @param QueryResultInterface $queryResult
     */
    public function __construct(QueryResultInterface $queryResult)
    {
        $this->queryResult = $queryResult;
    }

    /**
     * @return array
     * @Serializer\SerializedName("hydra:member")
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"__hydra_collection_response"})
     */
    public function getMembers(): array
    {
        return $this->queryResult->toArray();
    }

    /**
     * @return int
     * @Serializer\SerializedName("hydra:totalItems")
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"__hydra_collection_response"})
     */
    public function getTotalItems(): int
    {
        return $this->queryResult->count();
    }
}
