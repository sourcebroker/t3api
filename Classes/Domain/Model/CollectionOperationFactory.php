<?php

namespace SourceBroker\T3api\Domain\Model;

class CollectionOperationFactory
{
    public function create(string $key, ApiResource $apiResource, array $params): CollectionOperation
    {
        return new CollectionOperation($key, $apiResource, $params);
    }
}
