<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Domain\Model\ResponseCacheSettings;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ResponseCacheSettingsTest extends UnitTestCase
{
    #[Test]
    public function createWithoutAttributesOrBaseIsDisabledWithDefaults(): void
    {
        $settings = ResponseCacheSettings::create();

        self::assertFalse($settings->isEnabled());
        self::assertSame(ResponseCacheSettings::DEFAULT_LIFETIME_IN_SECONDS, $settings->getLifetime());
        self::assertSame('', $settings->getReadCondition());
        self::assertSame('', $settings->getWriteCondition());
        self::assertSame([], $settings->getIdentifierExpressions());
        self::assertSame([], $settings->getTags());
        self::assertSame([], $settings->getTagExpressions());
        self::assertSame([], $settings->getMemberTagExpressions());
    }

    #[Test]
    public function createAppliesGivenValues(): void
    {
        $settings = ResponseCacheSettings::create([
            'lifetime' => 3600,
            'readCondition' => "request.query.get('refresh') == null",
            'writeCondition' => "request.query.get('nocache') == null",
            'identifierExpressions' => [
                "request.headers.get('X-App-Version')",
                "context.getPropertyFromAspect('frontend.user', 'groupIds', '')",
            ],
            'tags' => ['pages', 'tt_content'],
            'tagExpressions' => ["user.isLoggedIn() ? 'fe_user_' ~ user.getUid() : ''"],
            'memberTagExpressions' => ["'author_' ~ object.getAuthor().getUid()"],
        ]);

        self::assertSame(3600, $settings->getLifetime());
        self::assertSame("request.query.get('refresh') == null", $settings->getReadCondition());
        self::assertSame("request.query.get('nocache') == null", $settings->getWriteCondition());
        self::assertSame(
            [
                "request.headers.get('X-App-Version')",
                "context.getPropertyFromAspect('frontend.user', 'groupIds', '')",
            ],
            $settings->getIdentifierExpressions()
        );
        self::assertSame(['pages', 'tt_content'], $settings->getTags());
        self::assertSame(
            ["user.isLoggedIn() ? 'fe_user_' ~ user.getUid() : ''"],
            $settings->getTagExpressions()
        );
        self::assertSame(
            ["'author_' ~ object.getAuthor().getUid()"],
            $settings->getMemberTagExpressions()
        );
    }

    #[Test]
    public function createWithNonEmptyAttributesWithoutEnabledKeyEnablesCaching(): void
    {
        self::assertTrue(ResponseCacheSettings::create(['lifetime' => 3600])->isEnabled());
    }

    #[Test]
    public function createWithEmptyAttributesInheritsEnabledBaseState(): void
    {
        $enabledBase = ResponseCacheSettings::create(['lifetime' => 3600]);

        self::assertTrue(ResponseCacheSettings::create([], $enabledBase)->isEnabled());
    }

    #[Test]
    public function createWithEmptyAttributesInheritsDisabledBaseState(): void
    {
        $disabledBase = ResponseCacheSettings::create();

        self::assertFalse(ResponseCacheSettings::create([], $disabledBase)->isEnabled());
    }

    #[Test]
    public function createWithExplicitEnabledFalseDisablesCachingEvenWithOtherAttributes(): void
    {
        $settings = ResponseCacheSettings::create(['enabled' => false, 'lifetime' => 60]);

        self::assertFalse($settings->isEnabled());
    }

    #[Test]
    public function createWithExplicitEnabledTrueOverridesDisabledBase(): void
    {
        $disabledBase = ResponseCacheSettings::create();

        self::assertTrue(ResponseCacheSettings::create(['enabled' => true], $disabledBase)->isEnabled());
    }

    /**
     * Mirrors an operation declaring `"attributes"={"cache"={"lifetime"=60}}` while the resource
     * itself declares no `cache` block at all - the operation alone gets caching enabled.
     */
    #[Test]
    public function createWithNonEmptyAttributesEnablesCachingEvenWhenBaseIsDisabled(): void
    {
        $disabledBase = ResponseCacheSettings::create();

        self::assertTrue(ResponseCacheSettings::create(['lifetime' => 60], $disabledBase)->isEnabled());
    }

    #[Test]
    public function createOverridesBaseValuesWhenAttributesAreGiven(): void
    {
        $base = ResponseCacheSettings::create(['enabled' => true, 'lifetime' => 3600]);

        $overridden = ResponseCacheSettings::create(['enabled' => true, 'lifetime' => 60], $base);

        self::assertTrue($overridden->isEnabled());
        self::assertSame(60, $overridden->getLifetime());
    }

    #[Test]
    public function createInheritsBaseValuesWhenAttributeKeysAreAbsent(): void
    {
        $base = ResponseCacheSettings::create([
            'enabled' => true,
            'lifetime' => 3600,
            'readCondition' => "request.query.get('refresh') == null",
            'writeCondition' => "request.query.get('nocache') == null",
            'identifierExpressions' => ["request.headers.get('X-App-Version')"],
            'tags' => ['pages'],
            'tagExpressions' => ["'fe_user_' ~ user.getUid()"],
            'memberTagExpressions' => ["'author_' ~ object.getAuthor().getUid()"],
        ]);

        $inherited = ResponseCacheSettings::create([], $base);

        self::assertTrue($inherited->isEnabled());
        self::assertSame(3600, $inherited->getLifetime());
        self::assertSame("request.query.get('refresh') == null", $inherited->getReadCondition());
        self::assertSame("request.query.get('nocache') == null", $inherited->getWriteCondition());
        self::assertSame(["request.headers.get('X-App-Version')"], $inherited->getIdentifierExpressions());
        self::assertSame(["'fe_user_' ~ user.getUid()"], $inherited->getTagExpressions());
        self::assertSame(['pages'], $inherited->getTags());
        self::assertSame(["'author_' ~ object.getAuthor().getUid()"], $inherited->getMemberTagExpressions());
    }

    /**
     * Mirrors an operation declaring only `"identifierExpressions"` in its own
     * `attributes={"cache"={...}}` block - the given array overrides the resource-level value
     * wholesale (no per-entry merging), while the untouched `readCondition` key still cascades
     * down from the resource, same as every other cache setting key.
     */
    #[Test]
    public function createOverridesIdentifierExpressionsWhileInheritingReadConditionFromBase(): void
    {
        $base = ResponseCacheSettings::create([
            'readCondition' => "request.query.get('refresh') == null",
            'identifierExpressions' => ["request.headers.get('X-App-Version')"],
        ]);

        $overridden = ResponseCacheSettings::create(
            ['identifierExpressions' => ["request.headers.get('X-Tenant')"]],
            $base
        );

        self::assertSame("request.query.get('refresh') == null", $overridden->getReadCondition());
        self::assertSame(["request.headers.get('X-Tenant')"], $overridden->getIdentifierExpressions());
    }

    /**
     * Mirrors an operation declaring only `"tags"` in its own `attributes={"cache"={...}}` block -
     * the given key overrides the resource-level value, while the untouched `readCondition` key
     * still cascades down from the resource, same as every other cache setting key.
     */
    #[Test]
    public function createOverridesTagsWhileInheritingReadConditionFromBase(): void
    {
        $base = ResponseCacheSettings::create([
            'readCondition' => "request.query.get('refresh') == null",
            'tags' => ['resource_tag'],
        ]);

        $overridden = ResponseCacheSettings::create(['tags' => ['operation_tag']], $base);

        self::assertSame("request.query.get('refresh') == null", $overridden->getReadCondition());
        self::assertSame(['operation_tag'], $overridden->getTags());
    }

    /**
     * Mirrors an operation declaring only `"tagExpressions"` in its own
     * `attributes={"cache"={...}}` block - the given key overrides the resource-level value, while
     * the untouched `readCondition` key still cascades down from the resource, same as every other
     * cache setting key.
     */
    #[Test]
    public function createOverridesTagExpressionsWhileInheritingReadConditionFromBase(): void
    {
        $base = ResponseCacheSettings::create([
            'readCondition' => "request.query.get('refresh') == null",
            'tagExpressions' => ["'resource_' ~ user.getUid()"],
        ]);

        $overridden = ResponseCacheSettings::create(
            ['tagExpressions' => ["'operation_' ~ user.getUid()"]],
            $base
        );

        self::assertSame("request.query.get('refresh') == null", $overridden->getReadCondition());
        self::assertSame(["'operation_' ~ user.getUid()"], $overridden->getTagExpressions());
    }

    #[Test]
    public function createInheritsTagExpressionsWhenAttributesAreAbsent(): void
    {
        $base = ResponseCacheSettings::create(['tagExpressions' => ["'resource_' ~ user.getUid()"]]);

        $inherited = ResponseCacheSettings::create([], $base);

        self::assertSame(["'resource_' ~ user.getUid()"], $inherited->getTagExpressions());
    }

    /**
     * Mirrors an operation declaring only `"memberTagExpressions"` in its own
     * `attributes={"cache"={...}}` block - the given key overrides the resource-level value, while
     * the untouched `readCondition` key still cascades down from the resource, same as every other
     * cache setting key.
     */
    #[Test]
    public function createOverridesMemberTagExpressionsWhileInheritingReadConditionFromBase(): void
    {
        $base = ResponseCacheSettings::create([
            'readCondition' => "request.query.get('refresh') == null",
            'memberTagExpressions' => ["'resource_author_' ~ object.getAuthor().getUid()"],
        ]);

        $overridden = ResponseCacheSettings::create(
            ['memberTagExpressions' => ["'operation_author_' ~ object.getAuthor().getUid()"]],
            $base
        );

        self::assertSame("request.query.get('refresh') == null", $overridden->getReadCondition());
        self::assertSame(
            ["'operation_author_' ~ object.getAuthor().getUid()"],
            $overridden->getMemberTagExpressions()
        );
    }

    #[Test]
    public function createInheritsMemberTagExpressionsWhenAttributesAreAbsent(): void
    {
        $base = ResponseCacheSettings::create([
            'memberTagExpressions' => ["'author_' ~ object.getAuthor().getUid()"],
        ]);

        $inherited = ResponseCacheSettings::create([], $base);

        self::assertSame(
            ["'author_' ~ object.getAuthor().getUid()"],
            $inherited->getMemberTagExpressions()
        );
    }

    #[Test]
    public function createWithoutAttributesOrBaseWasNotExplicitlyConfigured(): void
    {
        self::assertFalse(ResponseCacheSettings::create()->wasExplicitlyConfigured());
    }

    #[Test]
    public function createWithNonEmptyAttributesWasExplicitlyConfigured(): void
    {
        self::assertTrue(ResponseCacheSettings::create(['lifetime' => 3600])->wasExplicitlyConfigured());
    }

    /**
     * Mirrors an operation declaring no `attributes.cache` block of its own while the resource
     * level does - the resource-level block cascades its resolved values (e.g. `enabled`) onto the
     * operation, but the operation's own settings object must NOT be reported as explicitly
     * configured, since it did not declare anything itself. The flag is always derived from the
     * CURRENT `create()` call's `$attributes`, never inherited from the base, even though the base
     * itself stays explicitly configured.
     */
    #[Test]
    public function createWithEmptyAttributesAndExplicitBaseIsNotExplicitlyConfiguredWhileBaseStaysExplicit(): void
    {
        $explicitBase = ResponseCacheSettings::create(['lifetime' => 3600]);

        $inherited = ResponseCacheSettings::create([], $explicitBase);

        self::assertFalse($inherited->wasExplicitlyConfigured());
        self::assertTrue($explicitBase->wasExplicitlyConfigured());
    }
}
