<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Domain\Repository;

use ReflectionClass;
use ReflectionException;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Factory\ApiResourceFactory;
use SourceBroker\T3api\Service\ReflectionService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ApiResourceRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/t3api'];
    protected CacheManager $cacheManager;
    protected ApiResourceFactory $apiResourceFactory;
    protected ReflectionService $reflectionService;

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = $this->getMockBuilder(CacheManager::class)->disableOriginalConstructor()->getMock();
        $this->reflectionService = $this->getMockBuilder(ReflectionService::class)->disableOriginalConstructor()->getMock();
        $this->apiResourceFactory = $this->getMockBuilder(ApiResourceFactory::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     *
     * @throws ReflectionException
     */
    public function getAllDomainModelsReturnsAllClasses(): void
    {
        $apiResourceRepository = new ApiResourceRepository(
            $this->cacheManager,
            $this->reflectionService,
            $this->apiResourceFactory
        );

        // iterator_to_arrays converts the Generator object to an array because Generator can not be serialized
        self::assertEquals(
            [],
            iterator_to_array(self::callProtectedMethod('getAllDomainModels', [], $apiResourceRepository))
        );
    }

    /**
     * @param $methodName
     * @param array $arguments
     * @param object|null $object
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected static function callProtectedMethod($methodName, array $arguments = [], object $object = null)
    {
        $serializerMetadataServiceReflection = new ReflectionClass(ApiResourceRepository::class);
        $method = $serializerMetadataServiceReflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object ? $object : null, $arguments);
    }
}
