<?php

declare(strict_types=1);

namespace SourceBroker\T3api\EventListener;

use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;
use SourceBroker\T3api\Response\HydraCollectionResponse;

class AddHydraCollectionResponseSerializationGroupEventListener
{
    public function __invoke(AfterCreateContextForOperationEvent $createContextForOperationEvent): void
    {
        $operation =  $createContextForOperationEvent->getOperation();
        $context = $createContextForOperationEvent->getContext();

        $collectionResponseClass = Configuration::getCollectionResponseClass();
        if (
            $createContextForOperationEvent->getOperation() instanceof CollectionOperation
            && $context->hasAttribute('groups')
            && $operation->isMethodGet()
            && ($collectionResponseClass === HydraCollectionResponse::class || is_subclass_of($collectionResponseClass, HydraCollectionResponse::class))
        ) {
            $context->setGroups(array_merge(
                $context->getAttribute('groups'),
                ['__hydra_collection_response']
            ));
        }
    }
}
