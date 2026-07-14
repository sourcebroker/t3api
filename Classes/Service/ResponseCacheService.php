<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\ExpressionLanguage\Resolver;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResponseCacheService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ?Resolver $conditionResolver = null;

    public function __construct(
        protected readonly FrontendInterface $cache,
        protected readonly Context $context
    ) {}

    /**
     * Returns the cache entry identifier for given operation and request
     * or null when the request must not be served from cache.
     */
    public function buildEntryIdentifier(OperationInterface $operation, array $route, Request $request): ?string
    {
        if ($request->getMethod() !== Request::METHOD_GET) {
            return null;
        }

        $responseCacheSettings = $operation->getResponseCacheSettings();
        if (!$responseCacheSettings->isEnabled()) {
            return null;
        }

        $queryParams = $request->query->all();
        unset($queryParams[ResourceEnhancer::PARAMETER_NAME]);

        // `FilterAccessChecker` is not re-evaluated on the cache path (unlike the operation-level
        // `security` expression, see `OperationResponseCache`) - a query targeting a filter guarded
        // by a security condition must therefore bypass the cache entirely.
        if ($this->hasSecuredFilterParameter($operation, $queryParams)) {
            return null;
        }

        $allowedQueryParams = array_intersect_key(
            $queryParams,
            array_flip($this->getAllowedParameterNames($operation))
        );
        ksort($allowedQueryParams);

        $keyParts = [
            $this->getSiteIdentifier(),
            $this->getLanguageId(),
            $request->getPathInfo(),
            http_build_query($allowedQueryParams),
        ];

        // An empty `identifierExpressions` array (the default) leaves the loop below a no-op, so
        // entries built without this setting hash identically to before it existed.
        foreach ($responseCacheSettings->getIdentifierExpressions() as $identifierExpression) {
            $identifierExpressionValue = $this->evaluateIdentifierExpression($identifierExpression, $operation, $route, $request);
            if ($identifierExpressionValue === null) {
                return null;
            }
            $keyParts[] = $identifierExpressionValue;
        }

        return md5(implode('|', $keyParts));
    }

    /**
     * Whether the response for given request may be served from cache. Controlled
     * by the `readCondition` expression - an empty condition always allows reading.
     */
    public function isReadAllowed(OperationInterface $operation, array $route, Request $request): bool
    {
        return $this->isConditionSatisfied(
            $operation->getResponseCacheSettings()->getReadCondition(),
            $operation,
            $route,
            $request
        );
    }

    /**
     * Whether the response for given request may be stored in cache. Controlled
     * by the `writeCondition` expression - an empty condition always allows storing.
     */
    public function isWriteAllowed(OperationInterface $operation, array $route, Request $request): bool
    {
        return $this->isConditionSatisfied(
            $operation->getResponseCacheSettings()->getWriteCondition(),
            $operation,
            $route,
            $request
        );
    }

    public function get(string $entryIdentifier): ?string
    {
        try {
            $data = $this->cache->get($entryIdentifier);
        } catch (\Throwable $throwable) {
            $this->logger?->error('t3api response cache read failed', ['exception' => $throwable]);

            return null;
        }

        return is_string($data) ? $data : null;
    }

    /**
     * @param string[] $tags
     */
    public function store(string $entryIdentifier, string $output, array $tags, int $lifetime): void
    {
        try {
            $this->cache->set($entryIdentifier, $output, $tags, $lifetime);
        } catch (\Throwable $throwable) {
            $this->logger?->error('t3api response cache write failed', ['exception' => $throwable]);
        }
    }

    /**
     * Flushes every stored entry carrying any of the given tags - used for the declarative
     * `cacheInvalidation` `tags` setting, evaluated after a non-GET operation completes
     * successfully.
     *
     * @param string[] $tags
     */
    public function flushByTags(array $tags): void
    {
        try {
            $this->cache->flushByTags($tags);
        } catch (\Throwable $throwable) {
            $this->logger?->error('t3api response cache flush failed', ['exception' => $throwable, 'tags' => $tags]);
        }
    }

    /**
     * Whether the given string is a valid cache tag for this cache's frontend - used to validate
     * the declarative `cache`/`cacheInvalidation` `tags` literals, since an invalid tag reaching
     * `set()`/`flushByTags()` directly would throw.
     */
    public function isValidTag(string $tag): bool
    {
        return $this->cache->isValidTag($tag);
    }

    /**
     * Evaluates the `cache.tagExpressions` setting for a response about to be stored. Fail-closed,
     * mirroring `evaluateIdentifierExpression()`'s philosophy: an entry whose invalidation contract
     * cannot be guaranteed - because an expression errored, or produced a tag the cache frontend
     * rejects - must not be stored at all, so a single failing entry returns `null` (store nothing)
     * rather than a partial tag set. An empty string result is a valid "no tag from this
     * expression" result (the idiom for conditional tagging) and contributes nothing.
     *
     * `$additionalVariables` is how `OperationResponseCache` reuses this same fail-closed
     * evaluation for `cache.memberTagExpressions`, adding `object` (the member being evaluated) on
     * top of the standard variable set - `cache.tagExpressions` itself is always called without it,
     * since it is evaluated once for the whole response, before any entity is known to exist.
     *
     * @param string[] $tagExpressions
     * @param array<string, mixed> $additionalVariables
     * @return string[]|null the resolved, non-empty tags, or null when the entry must not be stored
     */
    public function evaluateStoreTagExpressions(
        array $tagExpressions,
        OperationInterface $operation,
        array $route,
        Request $request,
        array $additionalVariables = []
    ): ?array {
        $tags = [];
        foreach ($tagExpressions as $tagExpression) {
            $tag = $this->evaluateStoreTagExpression($tagExpression, $operation, $route, $request, $additionalVariables);
            if ($tag === null) {
                return null;
            }

            if ($tag !== '') {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    /**
     * Evaluates the `cacheInvalidation.tagExpressions` setting after a successful write. Fail-soft,
     * unlike the store side: the write itself already succeeded, so a broken expression must not
     * break the response - an evaluation error or an invalid resulting tag merely skips that one
     * tag, logged as a warning, while every other tag (static or expression-derived) still flushes.
     *
     * `$additionalVariables` is how `OperationResponseCache` adds `object` - the persisted entity of
     * the write operation, or `null` on a `DELETE` (or when a custom handler returns nothing) - on
     * top of the standard variable set.
     *
     * @param string[] $tagExpressions
     * @param array<string, mixed> $additionalVariables
     * @return string[]
     */
    public function evaluateFlushTagExpressions(
        array $tagExpressions,
        OperationInterface $operation,
        array $route,
        Request $request,
        array $additionalVariables = []
    ): array {
        $tags = [];
        foreach ($tagExpressions as $tagExpression) {
            $tag = $this->evaluateFlushTagExpression($tagExpression, $operation, $route, $request, $additionalVariables);
            if ($tag !== null && $tag !== '') {
                $tags[] = $tag;
            }
        }

        return array_values(array_unique($tags));
    }

    /**
     * Store-side single-expression evaluation: logs at error level and signals "do not store"
     * (`null`) both when the expression itself fails and when it produces a non-empty tag the
     * cache frontend rejects.
     *
     * @param array<string, mixed> $additionalVariables
     */
    private function evaluateStoreTagExpression(
        string $tagExpression,
        OperationInterface $operation,
        array $route,
        Request $request,
        array $additionalVariables = []
    ): ?string {
        try {
            $tag = $this->evaluateTagExpression($tagExpression, $operation, $route, $request, $additionalVariables);
        } catch (\Throwable $throwable) {
            $this->logger?->error('t3api response cache tag expression evaluation failed - entry not stored', [
                'tagExpression' => $tagExpression,
                'exception' => $throwable,
            ]);

            return null;
        }

        if ($tag !== '' && !$this->isValidTag($tag)) {
            $this->logger?->error('t3api response cache tag expression produced an invalid tag - entry not stored', [
                'tagExpression' => $tagExpression,
                'tag' => $tag,
            ]);

            return null;
        }

        return $tag;
    }

    /**
     * Flush-side single-expression evaluation: logs at warning level and signals "skip this tag"
     * (`null`) both when the expression itself fails and when it produces a non-empty tag the
     * cache frontend rejects - the other declared tags still flush.
     *
     * @param array<string, mixed> $additionalVariables
     */
    private function evaluateFlushTagExpression(
        string $tagExpression,
        OperationInterface $operation,
        array $route,
        Request $request,
        array $additionalVariables = []
    ): ?string {
        try {
            $tag = $this->evaluateTagExpression($tagExpression, $operation, $route, $request, $additionalVariables);
        } catch (\Throwable $throwable) {
            $this->logger?->warning('t3api response cache tag expression evaluation failed - skipped', [
                'tagExpression' => $tagExpression,
                'exception' => $throwable,
            ]);

            return null;
        }

        if ($tag !== '' && !$this->isValidTag($tag)) {
            $this->logger?->warning('t3api response cache tag expression produced an invalid tag - skipped', [
                'tagExpression' => $tagExpression,
                'tag' => $tag,
            ]);

            return null;
        }

        return $tag;
    }

    /**
     * Evaluates a single `tagExpressions` entry with the same resolver and variable set as
     * `readCondition`/`identifierExpressions`, plus whatever `$additionalVariables` the caller adds
     * on top (see `evaluateStoreTagExpressions()`/`evaluateFlushTagExpressions()`). Left to throw on
     * evaluation failure - the store and flush sides apply their own failure policy (see
     * `evaluateStoreTagExpression()` and `evaluateFlushTagExpression()`).
     *
     * @param array<string, mixed> $additionalVariables
     */
    private function evaluateTagExpression(
        string $tagExpression,
        OperationInterface $operation,
        array $route,
        Request $request,
        array $additionalVariables = []
    ): string {
        return (string)$this->getConditionResolver()->evaluate(
            $tagExpression,
            $this->getConditionVariables($operation, $route, $request, $additionalVariables)
        );
    }

    /**
     * `clearCachePostProc` hook - flushes response cache entries when caches
     * are cleared from the TYPO3 backend.
     */
    public function clearCachePostProc(array $params): void
    {
        if (($params['cacheCmd'] ?? '') === 'all') {
            $this->cache->flush();

            return;
        }

        $tags = array_keys($params['tags'] ?? []);
        if ($tags !== []) {
            $this->cache->flushByTags($tags);
        }
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    protected function hasSecuredFilterParameter(OperationInterface $operation, array $queryParams): bool
    {
        if (!$operation instanceof CollectionOperation) {
            return false;
        }

        foreach ($operation->getFilters() as $filter) {
            if (!empty($filter->getStrategy()->getCondition()) && array_key_exists($filter->getParameterName(), $queryParams)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    protected function getAllowedParameterNames(OperationInterface $operation): array
    {
        if (!$operation instanceof CollectionOperation) {
            return [];
        }

        $allowedParameterNames = [
            $operation->getPagination()->getPageParameterName(),
            $operation->getPagination()->getItemsPerPageParameterName(),
            $operation->getPagination()->getEnabledParameterName(),
        ];
        foreach ($operation->getFilters() as $filter) {
            $allowedParameterNames[] = $filter->getParameterName();
        }

        return array_unique($allowedParameterNames);
    }

    /**
     * Evaluates a single entry of the `identifierExpressions` cache setting with the same resolver
     * and variable set as `readCondition`/`writeCondition`. Mirrors their fail-closed pattern on
     * evaluation errors: an identifier that could not be determined must not fall back to one
     * shared with requests it was meant to be kept separate from, so `null` here means "do not
     * cache this request" rather than "treat as empty". A null/false expression result, on the
     * other hand, casts to `''` and is a perfectly valid - if unhelpful - identifier contribution.
     */
    protected function evaluateIdentifierExpression(
        string $identifierExpression,
        OperationInterface $operation,
        array $route,
        Request $request
    ): ?string {
        try {
            return (string)$this->getConditionResolver()->evaluate(
                $identifierExpression,
                $this->getConditionVariables($operation, $route, $request)
            );
        } catch (\Throwable $throwable) {
            $this->logger?->error('t3api response cache identifier expression evaluation failed', [
                'identifierExpression' => $identifierExpression,
                'exception' => $throwable,
            ]);

            return null;
        }
    }

    /**
     * Evaluates a cache condition expression. Evaluation errors count as "not
     * satisfied" (fail-closed) - a broken expression must never cause a stale
     * or unauthorized response to be served or stored.
     */
    protected function isConditionSatisfied(
        string $condition,
        OperationInterface $operation,
        array $route,
        Request $request
    ): bool {
        if ($condition === '') {
            return true;
        }

        try {
            return (bool)$this->getConditionResolver()->evaluate(
                $condition,
                $this->getConditionVariables($operation, $route, $request)
            );
        } catch (\Throwable $throwable) {
            $this->logger?->error('t3api response cache condition evaluation failed - treated as not satisfied', [
                'condition' => $condition,
                'exception' => $throwable,
            ]);

            return false;
        }
    }

    /**
     * Mirrors the variable set of `AbstractAccessChecker` (used by the `security`
     * expressions) and additionally exposes the Symfony `request` object and the
     * matched route parameters. `$additionalVariables` layers on top of this standard set - used
     * exclusively by the tag-expression evaluators to add `object` (see
     * `evaluateStoreTagExpressions()`/`evaluateFlushTagExpressions()`); every other caller
     * (conditions, `identifierExpressions`, the response-level `tagExpressions`) leaves it empty,
     * since `object` is never available there (see :ref:`response-cache-conditions`).
     *
     * @param array<string, mixed> $route
     * @param array<string, mixed> $additionalVariables
     * @return array<string, mixed>
     */
    protected function getConditionVariables(
        OperationInterface $operation,
        array $route,
        Request $request,
        array $additionalVariables = []
    ): array {
        // `_route` carries the spl_object_hash-based route name - nondeterministic across
        // processes, so exposing it to cache expressions would break identifier sharing.
        unset($route['_route']);

        return [
            'request' => $request,
            'route' => $route,
            't3apiOperation' => $operation,
            ...ExpressionLanguageService::getUserVariables($this->context),
            ...$additionalVariables,
        ];
    }

    protected function getConditionResolver(): Resolver
    {
        return $this->conditionResolver ??= GeneralUtility::makeInstance(Resolver::class, 't3api', []);
    }

    protected function getSiteIdentifier(): string
    {
        $site = $this->getTypo3Request()?->getAttribute('site');

        return $site instanceof Site ? $site->getIdentifier() : '';
    }

    protected function getLanguageId(): string
    {
        $language = $this->getTypo3Request()?->getAttribute('language');

        return $language instanceof SiteLanguage ? (string)$language->getLanguageId() : '';
    }

    protected function getTypo3Request(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
