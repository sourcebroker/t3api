<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Domain\Model\CacheInvalidationSettings;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Model\ResponseCacheSettings;
use SourceBroker\T3api\Service\ApiResourceConfigurationValidator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * `ApiResource` is a concrete class, not an interface, but its constructor requires a real
 * `ApiResourceAnnotation` and builds routes for every operation via `AbstractOperation`, which in
 * turn needs `RouteService::getFullApiBasePath()` - unavailable without a bootstrapped TYPO3 site.
 * Every `ApiResource`/`OperationInterface` involved here is therefore a plain PHPUnit mock
 * (`createMock()`) with only the methods the validator actually calls stubbed
 * (`getOperations()`/`getEntity()` on the resource, `getKey()`/`isMethodGet()`/
 * `getResponseCacheSettings()`/`getCacheInvalidationSettings()` on each operation) - no real
 * operation, route, or annotation is ever constructed.
 */
class ApiResourceConfigurationValidatorTest extends UnitTestCase
{
    private ApiResourceConfigurationValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ApiResourceConfigurationValidator();
    }

    #[Test]
    public function validateThrowsWhenNonGetOperationExplicitlyConfiguresResponseCache(): void
    {
        $operation = $this->createOperation(
            key: 'add',
            isMethodGet: false,
            responseCacheSettings: ResponseCacheSettings::create(['lifetime' => 3600])
        );

        try {
            $this->validator->validate($this->createApiResourceWithOperations('Vendor\\Ext\\Domain\\Model\\Book', $operation));
            self::fail('Expected InvalidArgumentException was not thrown.');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(1783923801687, $invalidArgumentException->getCode());
            self::assertStringContainsString('add', $invalidArgumentException->getMessage());
            self::assertStringContainsString('Vendor\\Ext\\Domain\\Model\\Book', $invalidArgumentException->getMessage());
        }
    }

    #[Test]
    public function validateThrowsWhenGetOperationExplicitlyConfiguresCacheInvalidation(): void
    {
        $operation = $this->createOperation(
            key: 'get',
            isMethodGet: true,
            cacheInvalidationSettings: CacheInvalidationSettings::create(['tags' => ['tx_ext_domain_model_book']])
        );

        try {
            $this->validator->validate($this->createApiResourceWithOperations('Vendor\\Ext\\Domain\\Model\\Book', $operation));
            self::fail('Expected InvalidArgumentException was not thrown.');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            self::assertSame(1783923801688, $invalidArgumentException->getCode());
            self::assertStringContainsString('get', $invalidArgumentException->getMessage());
            self::assertStringContainsString('Vendor\\Ext\\Domain\\Model\\Book', $invalidArgumentException->getMessage());
        }
    }

    /**
     * A non-GET operation whose (enabled) response-cache settings were only inherited from a
     * resource-level `cache` block - never declared on the operation itself - is a legitimate
     * cascade the runtime simply ignores for a write operation, not a configuration mistake. The
     * validator must key off `wasExplicitlyConfigured()`, not `isEnabled()`.
     */
    #[Test]
    public function validateDoesNotThrowWhenNonGetOperationOnlyInheritsEnabledResponseCache(): void
    {
        $explicitResourceLevelSettings = ResponseCacheSettings::create(['lifetime' => 3600]);
        $inheritedOperationSettings = ResponseCacheSettings::create([], $explicitResourceLevelSettings);

        self::assertTrue($inheritedOperationSettings->isEnabled());
        self::assertFalse($inheritedOperationSettings->wasExplicitlyConfigured());

        $operation = $this->createOperation(
            key: 'add',
            isMethodGet: false,
            responseCacheSettings: $inheritedOperationSettings
        );

        $apiResource = $this->createApiResourceWithOperations('Vendor\\Ext\\Domain\\Model\\Book', $operation);

        $this->validator->validate($apiResource);

        self::assertTrue($inheritedOperationSettings->isEnabled(), 'sanity check unchanged after validate()');
    }

    #[Test]
    public function validateDoesNotThrowForCleanResource(): void
    {
        $getOperation = $this->createOperation(key: 'get', isMethodGet: true);
        $addOperation = $this->createOperation(key: 'add', isMethodGet: false);

        $this->validator->validate(
            $this->createApiResourceWithOperations('Vendor\\Ext\\Domain\\Model\\Book', $getOperation, $addOperation)
        );

        self::assertFalse($getOperation->getResponseCacheSettings()->wasExplicitlyConfigured());
        self::assertFalse($addOperation->getCacheInvalidationSettings()->wasExplicitlyConfigured());
    }

    private function createOperation(
        string $key,
        bool $isMethodGet,
        ?ResponseCacheSettings $responseCacheSettings = null,
        ?CacheInvalidationSettings $cacheInvalidationSettings = null
    ): OperationInterface {
        $operation = $this->createMock(OperationInterface::class);
        $operation->method('getKey')->willReturn($key);
        $operation->method('isMethodGet')->willReturn($isMethodGet);
        $operation->method('getResponseCacheSettings')->willReturn(
            $responseCacheSettings ?? ResponseCacheSettings::create()
        );
        $operation->method('getCacheInvalidationSettings')->willReturn(
            $cacheInvalidationSettings ?? CacheInvalidationSettings::create()
        );

        return $operation;
    }

    private function createApiResourceWithOperations(string $entity, OperationInterface ...$operations): ApiResource
    {
        $apiResource = $this->createMock(ApiResource::class);
        $apiResource->method('getEntity')->willReturn($entity);
        $apiResource->method('getOperations')->willReturn($operations);

        return $apiResource;
    }
}
