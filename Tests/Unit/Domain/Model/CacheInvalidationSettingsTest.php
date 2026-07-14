<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Domain\Model\CacheInvalidationSettings;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CacheInvalidationSettingsTest extends UnitTestCase
{
    #[Test]
    public function createWithoutAttributesOrBaseHasNoTags(): void
    {
        self::assertSame([], CacheInvalidationSettings::create()->getTags());
        self::assertSame([], CacheInvalidationSettings::create()->getTagExpressions());
    }

    #[Test]
    public function createAppliesGivenTags(): void
    {
        $settings = CacheInvalidationSettings::create([
            'tags' => ['tx_myext_domain_model_recipe'],
        ]);

        self::assertSame(['tx_myext_domain_model_recipe'], $settings->getTags());
    }

    #[Test]
    public function createAppliesGivenTagExpressions(): void
    {
        $settings = CacheInvalidationSettings::create([
            'tagExpressions' => ["'fe_user_' ~ user.getUid()"],
        ]);

        self::assertSame(["'fe_user_' ~ user.getUid()"], $settings->getTagExpressions());
    }

    /**
     * Mirrors an operation declaring its own `attributes={"cacheInvalidation"={"tags"={...}}}`
     * block - the given tags override the resource-level value wholesale (no per-entry merging).
     */
    #[Test]
    public function createOverridesBaseTagsWhenAttributesAreGiven(): void
    {
        $base = CacheInvalidationSettings::create(['tags' => ['resource_tag']]);

        $overridden = CacheInvalidationSettings::create(['tags' => ['operation_tag']], $base);

        self::assertSame(['operation_tag'], $overridden->getTags());
    }

    /**
     * Mirrors an operation declaring only its own `tagExpressions` - the given key overrides the
     * resource-level value wholesale, while the untouched `tags` key still cascades down from the
     * resource, same as every other cacheInvalidation setting key.
     */
    #[Test]
    public function createOverridesTagExpressionsWhileInheritingTagsFromBase(): void
    {
        $base = CacheInvalidationSettings::create([
            'tags' => ['resource_tag'],
            'tagExpressions' => ["'resource_' ~ user.getUid()"],
        ]);

        $overridden = CacheInvalidationSettings::create(
            ['tagExpressions' => ["'operation_' ~ user.getUid()"]],
            $base
        );

        self::assertSame(['resource_tag'], $overridden->getTags());
        self::assertSame(["'operation_' ~ user.getUid()"], $overridden->getTagExpressions());
    }

    /**
     * Mirrors an operation declaring no `cacheInvalidation` block of its own - the resource-level
     * tags cascade down unchanged.
     */
    #[Test]
    public function createInheritsBaseTagsWhenAttributesAreAbsent(): void
    {
        $base = CacheInvalidationSettings::create(['tags' => ['resource_tag']]);

        $inherited = CacheInvalidationSettings::create([], $base);

        self::assertSame(['resource_tag'], $inherited->getTags());
    }

    #[Test]
    public function createInheritsBaseTagExpressionsWhenAttributesAreAbsent(): void
    {
        $base = CacheInvalidationSettings::create(['tagExpressions' => ["'fe_user_' ~ user.getUid()"]]);

        $inherited = CacheInvalidationSettings::create([], $base);

        self::assertSame(["'fe_user_' ~ user.getUid()"], $inherited->getTagExpressions());
    }

    #[Test]
    public function createWithoutAttributesOrBaseWasNotExplicitlyConfigured(): void
    {
        self::assertFalse(CacheInvalidationSettings::create()->wasExplicitlyConfigured());
    }

    #[Test]
    public function createWithNonEmptyAttributesWasExplicitlyConfigured(): void
    {
        self::assertTrue(
            CacheInvalidationSettings::create(['tags' => ['tx_myext_domain_model_recipe']])->wasExplicitlyConfigured()
        );
    }

    /**
     * Mirrors an operation declaring no `attributes.cacheInvalidation` block of its own while the
     * resource level does - the resource-level block cascades its resolved values (e.g. `tags`)
     * onto the operation, but the operation's own settings object must NOT be reported as
     * explicitly configured, since it did not declare anything itself. The flag is always derived
     * from the CURRENT `create()` call's `$attributes`, never inherited from the base, even though
     * the base itself stays explicitly configured.
     */
    #[Test]
    public function createWithEmptyAttributesAndExplicitBaseIsNotExplicitlyConfiguredWhileBaseStaysExplicit(): void
    {
        $explicitBase = CacheInvalidationSettings::create(['tags' => ['resource_tag']]);

        $inherited = CacheInvalidationSettings::create([], $explicitBase);

        self::assertFalse($inherited->wasExplicitlyConfigured());
        self::assertTrue($explicitBase->wasExplicitlyConfigured());
    }
}
