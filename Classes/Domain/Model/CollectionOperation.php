<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Model;

/**
 * Class CollectionOperation
 */
class CollectionOperation extends AbstractOperation
{
    /**
     * @inheritdoc
     */
    protected function getType(): string
    {
        return 'collection';
    }
}
