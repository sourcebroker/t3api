<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Repository;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use SourceBroker\T3api\Annotation\ApiFilter as ApiFilterAnnotation;
use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\ApiResource;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use ReflectionClass;
use ReflectionException;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;

/**
 * Class ApiResourceRepository
 */
class ApiResourceRepository
{
    /**
     * @var FrontendInterface
     */
    private $cache;

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
     * @return ApiResource[]
     */
    public function getAll()
    {
        $cacheIdentifier = 'ApiResourceRepository__getAll';

        $apiResources = $this->cache->get($cacheIdentifier);

        if ($apiResources !== false) {
            return $apiResources;
        }

        try {
            $annotationReader = new AnnotationReader();
        } catch (AnnotationException $exception) {
            // @todo log error to TYPO3
            return [];
        }
        $apiResources = [];

        foreach ($this->getAllDomainModels() as $domainModel) {
            try {
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
            } catch (ReflectionException $exception) {
                // @todo log error to TYPO3
            }
        }

        $this->cache->set($cacheIdentifier, $apiResources);

        return $apiResources;
    }

    /**
     * @return string[]
     */
    protected function getAllDomainModels()
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
                function ($class) {
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
}
