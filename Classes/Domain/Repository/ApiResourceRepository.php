<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Repository;

use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Factory\ApiResourceFactory;
use SourceBroker\T3api\Service\ReflectionService;
use SourceBroker\T3api\Service\RouteService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

class ApiResourceRepository
{
    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @var ApiResourceFactory
     */
    protected $apiResourceFactory;

    /**
     * @param CacheManager $cacheManager
     *
     * @throws NoSuchCacheException
     */
    public function injectCache(CacheManager $cacheManager): void
    {
        $this->cache = $cacheManager->getCache('t3api');
    }

    public function injectReflectionService(ReflectionService $reflectionService): void
    {
        $this->reflectionService = $reflectionService;
    }

    public function injectApiResourceFactory(ApiResourceFactory $apiResourceFactory): void
    {
        $this->apiResourceFactory = $apiResourceFactory;
    }

    /**
     * @return ApiResource[]
     */
    public function getAll(): array
    {
        $cacheIdentifier = $this->buildCacheIdentifier();

        $apiResources = $this->cache->get($cacheIdentifier);

        if ($apiResources !== false) {
            return $apiResources;
        }

        $apiResources = [];

        foreach ($this->getAllDomainModels() as $fqcn) {
            $apiResources[] = $this->apiResourceFactory->createApiResourceFromFqcn($fqcn);
        }

        $apiResources = array_filter($apiResources);

        $this->cache->set($cacheIdentifier, $apiResources);

        return $apiResources;
    }

    /**
     * @param string|object $entity Class name or object
     *
     * @return ApiResource|null
     */
    public function getByEntity($entity): ?ApiResource
    {
        $className = is_string($entity) ? $entity : get_class($entity);

        foreach ($this->getAll() as $apiResource) {
            if ($apiResource->getEntity() === $className) {
                return $apiResource;
            }
        }

        return null;
    }

    /**
     * @return iterable<string>
     */
    protected function getAllDomainModels(): iterable
    {
        foreach ($this->getAllDomainModelClassNames() as $className) {
            if (is_subclass_of($className, AbstractDomainObject::class)) {
                yield $className;
            }
        }
    }

    /**
     * @return iterable<string>
     */
    protected function getAllDomainModelClassNames(): iterable
    {
        foreach (Configuration::getApiResourcePathProviders() as $apiResourcePathProvider) {
            foreach ($apiResourcePathProvider->getAll() as $domainModelClassFile) {
                $className = $this->reflectionService->getClassNameFromFile($domainModelClassFile);
                if ($className) {
                    yield $className;
                }
            }
        }
    }

    protected function buildCacheIdentifier(): string
    {
        return 'ApiResourceRepository_getAll'
            . preg_replace('~[^\pL\d]+~u', '_', RouteService::getFullApiBasePath());
    }
}
