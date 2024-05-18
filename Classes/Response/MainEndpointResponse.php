<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Response;

use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;

class MainEndpointResponse
{
    public function __construct(protected readonly ApiResourceRepository $apiResourceRepository) {}

    /**
     * @throws \ReflectionException
     */
    public function getResources(): array
    {
        $resources = [];

        foreach ($this->apiResourceRepository->getAll() as $apiResource) {
            if (!$apiResource->getMainCollectionOperation() instanceof CollectionOperation) {
                continue;
            }

            $resources[$apiResource->getEntity()] = $apiResource->getMainCollectionOperation()->getRoute()->getPath();
        }

        return $resources;
    }
}
