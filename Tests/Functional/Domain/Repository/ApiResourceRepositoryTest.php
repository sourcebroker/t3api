<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use ReflectionClass;
use ReflectionException;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;

class ApiResourceRepositoryTest extends FunctionalTestCase
{
    /**
     * @test
     *
     * @throws ReflectionException
     */
    public function getAllDomainModelsReturnsAllClasses(): void
    {
        self::assertEquals(
            [],
            self::callProtectedMethod('getAllDomainModels', [], new ApiResourceRepository())
        );
    }

    /**
     * @param $methodName
     * @param array $arguments
     * @param object|null $object
     *
     * @throws ReflectionException
     * @return mixed
     */
    protected static function callProtectedMethod($methodName, array $arguments = [], object $object = null)
    {
        $serializerMetadataServiceReflection = new ReflectionClass(ApiResourceRepository::class);
        $method = $serializerMetadataServiceReflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object ? $object : null, $arguments);
    }
}
