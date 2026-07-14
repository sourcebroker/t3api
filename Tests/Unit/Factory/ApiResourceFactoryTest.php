<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Domain\Model\ResponseCacheSettings;
use SourceBroker\T3api\Factory\ApiResourceFactory;
use SourceBroker\T3api\Service\ApiResourceConfigurationValidator;
use SourceBroker\T3api\Tests\Unit\Domain\Model\PaginationTest;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\CacheableBook;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\OperationCacheableBook;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\PlainBook;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ApiResourceFactoryTest extends UnitTestCase
{
    #[Test]
    public function apiResourceBuiltFromCacheableEntityHasEnabledCacheSettingsWithAnnotationLifetime(): void
    {
        $responseCacheSettings = $this->buildApiResourceFor(CacheableBook::class)->getResponseCacheSettings();

        self::assertTrue($responseCacheSettings->isEnabled());
        self::assertSame(3600, $responseCacheSettings->getLifetime());
    }

    #[Test]
    public function apiResourceBuiltFromPlainEntityHasDisabledCacheSettings(): void
    {
        self::assertFalse($this->buildApiResourceFor(PlainBook::class)->getResponseCacheSettings()->isEnabled());
    }

    #[Test]
    public function createApiResourceFromFqcnReturnsNullWithoutApiResourceAnnotation(): void
    {
        $apiResourceConfigurationValidator = $this->createMock(ApiResourceConfigurationValidator::class);
        $apiResourceConfigurationValidator->expects(self::never())->method('validate');

        self::assertNull(
            (new ApiResourceFactory($apiResourceConfigurationValidator))->createApiResourceFromFqcn(PlainBook::class)
        );
    }

    /**
     * Configuration validation moved from a pre-construction annotation scan to
     * `ApiResourceConfigurationValidator`, run by the factory on the fully built `ApiResource`
     * (operations/routes attached) right before it is returned - so this test only proves the
     * factory *wires* the validator correctly. The bad-configuration fixtures the old
     * pre-construction assertion tests used are gone; the rules themselves are exercised directly
     * against the validator in `ApiResourceConfigurationValidatorTest`.
     *
     * `CacheableBook` is reused here because it declares an `@ApiResource` annotation but no
     * operations, so building its `ApiResource` never reaches `AbstractOperation`'s
     * `RouteService::getFullApiBasePath()` call (unavailable without a bootstrapped TYPO3 site -
     * see `buildApiResourceFor()` below). Going through the real `createApiResourceFromFqcn()`
     * (rather than constructing `ApiResource` directly) also exercises `Pagination::create()`,
     * which reads its `pagination_*` attribute keys unconditionally - in production they are
     * merged into every `@ApiResource` annotation's attributes by
     * `SourceBroker\T3api\Annotation\ApiResource::__construct()` reading
     * `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination']`, populated by
     * `ext_localconf.php` in production; that global is populated by hand here (`backupGlobals`
     * is enabled for this suite, so PHPUnit restores it after the test).
     */
    #[Test]
    public function createApiResourceFromFqcnCallsValidatorExactlyOnceWithTheBuiltApiResource(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination'] = PaginationTest::DEFAULT_API_RESOURCE_PAGINATION_ATTRIBUTES;

        $capturedApiResource = null;
        $apiResourceConfigurationValidator = $this->createMock(ApiResourceConfigurationValidator::class);
        $apiResourceConfigurationValidator->expects(self::once())
            ->method('validate')
            ->with(self::isInstanceOf(ApiResource::class))
            ->willReturnCallback(static function (ApiResource $apiResource) use (&$capturedApiResource): void {
                $capturedApiResource = $apiResource;
            });

        $apiResource = (new ApiResourceFactory($apiResourceConfigurationValidator))
            ->createApiResourceFromFqcn(CacheableBook::class);

        self::assertNotNull($apiResource);
        self::assertSame($apiResource, $capturedApiResource);
    }

    /**
     * `OperationCacheableBook` declares no resource-level `cache` block (base stays disabled)
     * but enables caching on its single item operation via a non-empty `attributes.cache`
     * block - the resource-level settings on the factory-built `ApiResource` must stay
     * disabled, while the same cascade `AbstractOperation::__construct()` performs for that
     * operation's raw `attributes.cache` block against the resource-level base must resolve
     * to enabled.
     *
     * Exercised as a direct `ResponseCacheSettings::create()` cascade instead of through a
     * real `ItemOperation` instance: constructing one reaches `RouteService::getFullApiBasePath()`,
     * which needs a resolvable TYPO3 site unavailable in a unit test.
     */
    #[Test]
    public function operationCanEnableCachingWhenResourceDeclaresNoCacheBlock(): void
    {
        $apiResource = $this->buildApiResourceFor(OperationCacheableBook::class);

        self::assertFalse($apiResource->getResponseCacheSettings()->isEnabled());

        $apiResourceAnnotation = (new AnnotationReader())->getClassAnnotation(
            new \ReflectionClass(OperationCacheableBook::class),
            ApiResourceAnnotation::class
        );
        $operationCacheAttributes = $apiResourceAnnotation->getItemOperations()['get']['attributes']['cache'] ?? [];

        $operationResponseCacheSettings = ResponseCacheSettings::create(
            $operationCacheAttributes,
            $apiResource->getResponseCacheSettings()
        );

        self::assertTrue($operationResponseCacheSettings->isEnabled());
    }

    /**
     * Builds a real `ApiResource` for the given FQCN without going through
     * `ApiResourceFactory::createApiResourceFromFqcn()`. `Pagination::create()` reads its
     * `pagination_*` attribute keys unconditionally (even a disabled/unused pagination still
     * touches them as fallback values), so constructing an `ApiResource` always needs those keys
     * present in `attributes` - in production they come from
     * `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination']`, populated by
     * `ext_localconf.php`, which is not loaded in this unit test. Pagination and upload defaults
     * are therefore supplied explicitly, and the FQCN's own declared `cache` attributes (if any)
     * are read via the real annotation reader and merged in, so this exercises the same cache
     * resolution `ApiResource::__construct()` performs in production.
     */
    private function buildApiResourceFor(string $fqcn): ApiResource
    {
        $apiResourceAnnotation = (new AnnotationReader())->getClassAnnotation(
            new \ReflectionClass($fqcn),
            ApiResourceAnnotation::class
        );

        $attributes = PaginationTest::DEFAULT_API_RESOURCE_PAGINATION_ATTRIBUTES;
        $attributes['upload'] = ['allowedFileExtensions' => ['jpg']];
        $attributes['cache'] = $apiResourceAnnotation instanceof ApiResourceAnnotation
            ? ($apiResourceAnnotation->getAttributes()['cache'] ?? [])
            : [];

        return new ApiResource($fqcn, new ApiResourceAnnotation(['attributes' => $attributes]));
    }
}
