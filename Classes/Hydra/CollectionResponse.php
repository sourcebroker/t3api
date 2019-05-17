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
     * @var array
     * @Serializer\SerializedName("hydra:member")
     * @Serializer\Groups({"__hydra_collection_response"})
     */
    protected $members;

    /**
     * @var integer
     * @Serializer\SerializedName("hydra:totalItems")
     * @Serializer\Groups({"__hydra_collection_response"})
     */
    protected $totalItems;

    /**
     * CollectionResponse constructor.
     *
     * @param QueryResultInterface $queryResult
     */
    public function __construct(QueryResultInterface $queryResult)
    {
        $this->queryResult = $queryResult;
        $this->members = $this->queryResult->toArray();
        $this->totalItems = $this->queryResult->count();
    }
}
