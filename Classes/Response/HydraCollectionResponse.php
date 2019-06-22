<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Response;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class CollectionResponse
 */
class HydraCollectionResponse extends AbstractCollectionResponse
{
    /**
     * @return array
     * @Serializer\SerializedName("hydra:member")
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"__hydra_collection_response"})
     */
    public function getMembers(): array
    {
        return parent::getMembers();
    }

    /**
     * @return int
     * @Serializer\SerializedName("hydra:totalItems")
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"__hydra_collection_response"})
     */
    public function getTotalItems(): int
    {
        return parent::getTotalItems();
    }
}
