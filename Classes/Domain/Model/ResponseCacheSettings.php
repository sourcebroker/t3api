<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Utility\ParameterUtility;

class ResponseCacheSettings extends AbstractOperationResourceSettings
{
    public const DEFAULT_LIFETIME_IN_SECONDS = 86400;

    protected bool $enabled = false;

    protected bool $explicitlyConfigured = false;

    protected int $lifetime = self::DEFAULT_LIFETIME_IN_SECONDS;

    protected string $readCondition = '';

    protected string $writeCondition = '';

    /**
     * @var string[]
     */
    protected array $identifierExpressions = [];

    /**
     * @var string[]
     */
    protected array $tags = [];

    /**
     * @var string[]
     */
    protected array $tagExpressions = [];

    /**
     * @var string[]
     */
    protected array $memberTagExpressions = [];

    /**
     * @param array $attributes
     * @param ResponseCacheSettings|null $base
     * @return ResponseCacheSettings
     */
    public static function create(
        array $attributes = [],
        ?AbstractOperationResourceSettings $base = null
    ): AbstractOperationResourceSettings {
        $responseCacheSettings = parent::create($attributes, $base);
        $responseCacheSettings->explicitlyConfigured = $attributes !== [];
        $responseCacheSettings->enabled = self::resolveEnabled($attributes, $responseCacheSettings->enabled);
        $responseCacheSettings->lifetime = isset($attributes['lifetime'])
            ? (int)$attributes['lifetime'] : $responseCacheSettings->lifetime;
        $responseCacheSettings->readCondition = $attributes['readCondition'] ?? $responseCacheSettings->readCondition;
        $responseCacheSettings->writeCondition = $attributes['writeCondition'] ?? $responseCacheSettings->writeCondition;
        $responseCacheSettings->identifierExpressions = $attributes['identifierExpressions']
            ?? $responseCacheSettings->identifierExpressions;
        $responseCacheSettings->tags = $attributes['tags'] ?? $responseCacheSettings->tags;
        $responseCacheSettings->tagExpressions = $attributes['tagExpressions']
            ?? $responseCacheSettings->tagExpressions;
        $responseCacheSettings->memberTagExpressions = $attributes['memberTagExpressions']
            ?? $responseCacheSettings->memberTagExpressions;

        return $responseCacheSettings;
    }

    /**
     * An explicit `enabled` key always wins. Otherwise a non-empty attributes block implies
     * caching is enabled - this lets a resource (or operation) turn caching on simply by
     * declaring any cache option, without repeating `"enabled"=true`. An empty block inherits
     * whatever the base (resource, or the disabled default) already decided.
     */
    private static function resolveEnabled(array $attributes, bool $baseEnabled): bool
    {
        if (isset($attributes['enabled'])) {
            return ParameterUtility::toBoolean($attributes['enabled']);
        }

        if ($attributes !== []) {
            return true;
        }

        return $baseEnabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * True when THIS settings object's own `attributes` block was non-empty - a resource-level
     * block, or a per-operation block declared directly on the operation. False when the
     * settings were produced purely by cascading a base's values forward (an empty per-operation
     * `attributes` block inheriting a resource-level block, or the disabled default), even though
     * `isEnabled()` may still report `true` in that case. Used to tell apart "this operation
     * itself configured `cache`" from "this operation merely inherited `cache`" - see
     * {@see \SourceBroker\T3api\Service\ApiResourceConfigurationValidator}.
     */
    public function wasExplicitlyConfigured(): bool
    {
        return $this->explicitlyConfigured;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function getReadCondition(): string
    {
        return $this->readCondition;
    }

    public function getWriteCondition(): string
    {
        return $this->writeCondition;
    }

    /**
     * Symfony expressions whose string results are each appended to the cache entry identifier -
     * lets the cache vary by something outside path/query, e.g. a request header (via `request`)
     * or a TYPO3 Context aspect (via `context.getPropertyFromAspect(...)`). An empty array (the
     * default) leaves the identifier unaffected.
     *
     * @return string[]
     */
    public function getIdentifierExpressions(): array
    {
        return $this->identifierExpressions;
    }

    /**
     * Extra literal tags added to a stored entry, on top of the automatic content tags and the
     * resource table seed. Plain strings only - for a tag whose value depends on the matched route
     * parameters (or anything else dynamic), use `tagExpressions` instead.
     *
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Symfony expressions whose non-empty string results are each added as an extra tag to a
     * stored entry, on top of `tags` and the automatic content tags - lets a stored entry be
     * tagged by anything visible to the expression, e.g. the matched route parameters (via
     * `route`, see :ref:`response-cache-conditions`) or the current user (via a project-provided
     * `user` variable). Evaluated with the same resolver and variable set as
     * `readCondition`/`identifierExpressions`. An empty string result is a valid "no tag from this
     * expression" result, the idiom for conditional tagging.
     *
     * @return string[]
     */
    public function getTagExpressions(): array
    {
        return $this->tagExpressions;
    }

    /**
     * Symfony expressions evaluated once per top-level result entity instead of once per response
     * - an item GET evaluates each expression once, against the single result entity; a collection
     * GET evaluates each expression once per collection member, the resulting tags unioned and
     * deduplicated across all members; an empty collection produces zero evaluations. Each
     * evaluation sees the same variable set as `tagExpressions` (see
     * :ref:`response-cache-tag-expressions`) plus `object`, bound to that one entity - the one
     * variable `tagExpressions` itself never has access to, since it is evaluated once for the
     * whole response, before any entity is known to exist. Non-empty string results are added as
     * extra tags to a stored entry, on top of `tags` and `tagExpressions`; an empty string result
     * is the same "no tag from this expression" idiom used elsewhere.
     *
     * @return string[]
     */
    public function getMemberTagExpressions(): array
    {
        return $this->memberTagExpressions;
    }
}
