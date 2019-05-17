<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Model;

/**
 * Class CollectionOperation
 */
class CollectionOperation extends AbstractOperation
{
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
