<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use SourceBroker\T3api\Annotation\ApiFilter as ApiFilterAnnotation;
use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Service\RouteService;
use SourceBroker\T3api\Service\ReflectionService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

/**
 * Class ApiResourceRepository
 */
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

        $annotationReader = new AnnotationReader();
        $apiResources = [];

        // @todo refactor loop below - decrease complexity and move to better place
        foreach ($this->getAllDomainModels() as $domainModel) {
            $modelReflection = new ReflectionClass($domainModel);

            /** @var ApiResourceAnnotation $apiResourceAnnotation */
            $apiResourceAnnotation = $annotationReader->getClassAnnotation(
                $modelReflection,
                ApiResourceAnnotation::class
            );

            if (!$apiResourceAnnotation) {
                continue;
            }

            $apiResource = new ApiResource($domainModel, $apiResourceAnnotation);
            $apiResources[] = $apiResource;

            $filterAnnotations = array_filter(
                $annotationReader->getClassAnnotations($modelReflection),
                function ($annotation) {
                    return $annotation instanceof ApiFilterAnnotation;
                }
            );

            foreach ($filterAnnotations as $filterAnnotation) {
                foreach (ApiFilter::createFromAnnotations($filterAnnotation) as $apiFilter) {
                    $apiResource->addFilter($apiFilter);
                }
            }
        }

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
