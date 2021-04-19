<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionException;
use SourceBroker\T3api\Annotation\ApiFilter as ApiFilterAnnotation;
use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Service\RouteService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
     * @param CacheManager $cacheManager
     *
     * @throws NoSuchCacheException
     */
    public function injectCache(CacheManager $cacheManager): void
    {
        $this->cache = $cacheManager->getCache('t3api');
    }

    /**
     * @throws ReflectionException
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
     * @return string[]
     */
    protected function getAllDomainModels(): array
    {
        $classes = [];
        foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extKey) {
            $extPath = ExtensionManagementUtility::extPath($extKey);
            foreach (glob($extPath . 'Classes/Domain/Model/*.php') as $domainModelClassFile) {
                $classes[] = $this->getClassNameFromFile($domainModelClassFile);
            }
        }

        return array_values(
            array_filter(
                $classes,
                static function ($class) {
                    return is_subclass_of($class, AbstractDomainObject::class);
                }
            )
        );
    }

    /**
     * @param string $filePath
     *
     * @return string|null
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $tokens = token_get_all(file_get_contents($filePath));
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespaceFound = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespaceFound = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }

        if ($namespaceFound) {
            return $namespace . '\\' . basename($filePath, '.php');
        }

        return null;
    }

    protected function buildCacheIdentifier(): string
    {
        return 'ApiResourceRepository_getAll'
            . preg_replace('~[^\pL\d]+~u', '_', RouteService::getFullApiBasePath());
    }
}
