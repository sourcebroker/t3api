<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

class CacheInvalidationSettings extends AbstractOperationResourceSettings
{
    protected bool $explicitlyConfigured = false;

    /**
     * @var string[]
     */
    protected array $tags = [];

    /**
     * @var string[]
     */
    protected array $tagExpressions = [];

    /**
     * @param array $attributes
     * @param CacheInvalidationSettings|null $base
     * @return CacheInvalidationSettings
     */
    public static function create(
        array $attributes = [],
        ?AbstractOperationResourceSettings $base = null
    ): AbstractOperationResourceSettings {
        $cacheInvalidationSettings = parent::create($attributes, $base);
        $cacheInvalidationSettings->explicitlyConfigured = $attributes !== [];
        $cacheInvalidationSettings->tags = $attributes['tags'] ?? $cacheInvalidationSettings->tags;
        $cacheInvalidationSettings->tagExpressions = $attributes['tagExpressions']
            ?? $cacheInvalidationSettings->tagExpressions;

        return $cacheInvalidationSettings;
    }

    /**
     * True when THIS settings object's own `attributes` block was non-empty - a resource-level
     * block, or a per-operation block declared directly on the operation. False when the
     * settings were produced purely by cascading a base's values forward (an empty per-operation
     * `attributes` block inheriting a resource-level block, or the empty default). Used to tell
     * apart "this operation itself configured `cacheInvalidation`" from "this operation merely
     * inherited it" - see {@see \SourceBroker\T3api\Service\ApiResourceConfigurationValidator}.
     */
    public function wasExplicitlyConfigured(): bool
    {
        return $this->explicitlyConfigured;
    }

    /**
     * Literal tags flushed after a non-GET operation executes successfully, regardless of whether
     * the operation itself has a cacheable response. Plain strings only - for a tag whose value
     * depends on the matched route parameters (or anything else dynamic), use `tagExpressions`
     * instead.
     *
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Symfony expressions whose non-empty string results are each flushed as an extra tag
     * alongside `tags`, after a non-GET operation executes successfully - lets a write flush
     * anything visible to the expression, e.g. the matched route parameters (via `route`, see
     * :ref:`response-cache-conditions`) or exactly the current user's entries (via a
     * project-provided `user` variable). Evaluated with the same resolver and variable set as
     * `readCondition`/`identifierExpressions`. An empty string result is a valid "no tag from this
     * expression" result, the idiom for conditional tagging.
     *
     * @return string[]
     */
    public function getTagExpressions(): array
    {
        return $this->tagExpressions;
    }
}
