<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Slot;

use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Response\HydraCollectionResponse;
use Symfony\Component\HttpFoundation\Request;

class AddHydraCollectionResponseSerializationGroup
{
    public function execute(OperationInterface $operation, Request $request, array $attributes): array
    {
        $collectionResponseClass = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'];
        if (
            $operation instanceof CollectionOperation
            && !empty($attributes['groups'])
            && $operation->isMethodGet()
            && ($collectionResponseClass === HydraCollectionResponse::class || is_subclass_of($collectionResponseClass, HydraCollectionResponse::class))
        ) {
            $attributes['groups'] = array_merge($attributes['groups'] ?? [], ['__hydra_collection_response']);
        }

        return [
            'operation' => $operation,
            'request' => $request,
            'attributes' => $attributes,
        ];
    }
}
