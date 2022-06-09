<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Response;

use ReflectionException;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;

/**
 * Class MainEndpointResponse
 */
class MainEndpointResponse
{
    /**
     * @var ApiResourceRepository
     */
    protected $apiResourceRepository;

    /**
     * @param ApiResourceRepository $apiResourceRepository
     */
    public function injectApiResourceRepository(ApiResourceRepository $apiResourceRepository): void
    {
        $this->apiResourceRepository = $apiResourceRepository;
    }

    /**
     * @throws ReflectionException
     * @return array
     */
    public function getResources(): array
    {
        $resources = [];

        foreach ($this->apiResourceRepository->getAll() as $apiResource) {
            if (!$apiResource->getMainCollectionOperation()) {
                continue;
            }

            $resources[$apiResource->getEntity()] = $apiResource->getMainCollectionOperation()->getRoute()->getPath();
        }

        return $resources;
    }
}
