<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Repository\CommonRepository;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Response\AbstractCollectionResponse;
use SourceBroker\T3api\Security\OperationAccessChecker;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Orchestrates the response cache around an operation: builds the cache entry
 * identifier, enforces the operation `security` expression, serves a cache
 * hit, or runs the operation and stores its output. Every hit/miss/bypass,
 * store and invalidation-flush outcome is also reported to
 * `ResponseCacheDebugHeaders`, which decorates `$response` with it in a
 * development context only.
 */
class OperationResponseCache implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected readonly ResponseCacheService $responseCacheService,
        protected readonly CacheTagCollector $cacheTagCollector,
        protected readonly OperationAccessChecker $operationAccessChecker,
        protected readonly DataMapper $dataMapper,
        protected readonly ResponseCacheDebugHeaders $responseCacheDebugHeaders
    ) {}

    /**
     * `$result` is an optional by-ref out-parameter: the caller's closure is expected to write the
     * operation handler's un-serialized result into it (see `AbstractDispatcher::processOperation()`),
     * the same way `$response` is already written to by the handler. This is how the `object`
     * expression variable reaches `cache.memberTagExpressions` (the top-level result entity/entities,
     * store path) and `cacheInvalidation.tagExpressions` (the written entity, flush path) - see
     * `storeWithTags()` and `flushInvalidationTagsAfterSuccessfulWrite()`.
     *
     * @param callable(): string $processOperation runs the operation and returns its serialized output
     * @throws OperationNotAllowedException
     */
    public function resolve(
        OperationInterface $operation,
        array $route,
        Request $request,
        callable $processOperation,
        ?ResponseInterface &$response = null,
        mixed &$result = null
    ): string {
        $cacheEntryIdentifier = $this->responseCacheService->buildEntryIdentifier($operation, $route, $request);
        if ($cacheEntryIdentifier === null) {
            return $this->processAndInvalidateCache($operation, $route, $request, $processOperation, $response, $result);
        }

        $accessBypassOutput = $this->enforceOperationSecurity($operation, $route, $request, $processOperation, $response);
        if ($accessBypassOutput !== null) {
            return $accessBypassOutput;
        }

        if ($this->responseCacheService->isReadAllowed($operation, $route, $request)) {
            $cachedOutput = $this->responseCacheService->get($cacheEntryIdentifier);
            if ($cachedOutput !== null) {
                $this->responseCacheDebugHeaders->hit($response, $cacheEntryIdentifier);

                return $cachedOutput;
            }

            $this->responseCacheDebugHeaders->lookupOutcome($response, 'miss');
        } else {
            $this->responseCacheDebugHeaders->lookupOutcome($response, 'bypass');
        }

        return $this->processAndStore(
            $operation,
            $route,
            $request,
            $cacheEntryIdentifier,
            $processOperation,
            $response,
            $result
        );
    }

    /**
     * Runs the operation and, on success, flushes the operation's declarative `cacheInvalidation`
     * tags - used on paths that never reach the normal cache-then-store flow below (a non-GET
     * request, a GET request without an enabled response cache, or an item-operation security
     * bypass).
     *
     * @param callable(): string $processOperation
     */
    private function processAndInvalidateCache(
        OperationInterface $operation,
        array $route,
        Request $request,
        callable $processOperation,
        ?ResponseInterface &$response,
        mixed &$result = null
    ): string {
        $output = $processOperation();
        $this->flushInvalidationTagsAfterSuccessfulWrite($operation, $route, $request, $response, $result);

        return $output;
    }

    /**
     * The cacheable-request path (`GET` only, see `ResponseCacheService::buildEntryIdentifier()`):
     * processes the operation and, for a successful, write-allowed response, stores the output
     * under the collected content tags plus the operation's declarative `cache` `tags`. A `GET`
     * operation never invalidates anything itself, so there is no flush step here - see
     * `flushInvalidationTagsAfterSuccessfulWrite()`.
     *
     * @param callable(): string $processOperation
     */
    private function processAndStore(
        OperationInterface $operation,
        array $route,
        Request $request,
        string $cacheEntryIdentifier,
        callable $processOperation,
        ?ResponseInterface &$response,
        mixed &$result = null
    ): string {
        $this->cacheTagCollector->start();
        $this->seedResourceTableTag($operation);
        try {
            $output = $processOperation();
        } finally {
            $cacheTags = $this->cacheTagCollector->stop();
        }

        if (
            ($response === null || $response->getStatusCode() === 200)
            && $this->responseCacheService->isWriteAllowed($operation, $route, $request)
        ) {
            $this->storeWithTags($operation, $route, $request, $cacheEntryIdentifier, $output, $cacheTags, $result, $response);
        }

        return $output;
    }

    /**
     * Combines the automatic content tags with the operation's declarative `cache` `tags`,
     * `tagExpressions` and `memberTagExpressions`, then stores the entry. A `tagExpressions` (or
     * `memberTagExpressions`) evaluation failure - an expression error, or a non-empty result that
     * is not a valid tag - means the entry's invalidation contract cannot be guaranteed, so the
     * entry is not stored at all (fail-closed, see
     * `ResponseCacheService::evaluateStoreTagExpressions()`); the logging for that failure already
     * happened there.
     *
     * @param string[] $cacheTags
     */
    private function storeWithTags(
        OperationInterface $operation,
        array $route,
        Request $request,
        string $cacheEntryIdentifier,
        string $output,
        array $cacheTags,
        mixed $result,
        ?ResponseInterface &$response
    ): void {
        $responseCacheSettings = $operation->getResponseCacheSettings();
        $tagExpressions = $responseCacheSettings->getTagExpressions();
        $expressionTags = $tagExpressions === []
            ? []
            : $this->responseCacheService->evaluateStoreTagExpressions($tagExpressions, $operation, $route, $request);
        if ($expressionTags === null) {
            return;
        }

        $memberTagExpressions = $responseCacheSettings->getMemberTagExpressions();
        $memberExpressionTags = $memberTagExpressions === []
            ? []
            : $this->evaluateMemberTagExpressions($memberTagExpressions, $operation, $route, $request, $result);
        if ($memberExpressionTags === null) {
            return;
        }

        $literalTags = $this->filterValidTags($responseCacheSettings->getTags());
        $tags = array_values(array_unique([...$cacheTags, ...$literalTags, ...$expressionTags, ...$memberExpressionTags]));
        $this->responseCacheService->store($cacheEntryIdentifier, $output, $tags, $responseCacheSettings->getLifetime());
        $this->responseCacheDebugHeaders->stored($response, $cacheEntryIdentifier, $tags);
    }

    /**
     * Evaluates `cache.memberTagExpressions` once per top-level result entity, each evaluation
     * seeing the standard tag-expression variable set (see
     * `ResponseCacheService::evaluateStoreTagExpressions()`) plus `object`, bound to that one
     * entity. An item GET's `$result` is the single result entity, evaluated once; a collection
     * GET's `$result` is an `AbstractCollectionResponse`, evaluated once per member, the resulting
     * tags unioned and deduplicated - an empty collection therefore contributes zero evaluations
     * and zero tags, without blocking the store. Fail-closed per member, mirroring
     * `evaluateStoreTagExpressions()`: a single member's expression failure means the entry's
     * invalidation contract cannot be guaranteed, so the whole entry is not stored (`null`) rather
     * than a partial per-member tag set.
     *
     * @param string[] $memberTagExpressions
     * @return string[]|null
     */
    private function evaluateMemberTagExpressions(
        array $memberTagExpressions,
        OperationInterface $operation,
        array $route,
        Request $request,
        mixed $result
    ): ?array {
        $tags = [];
        foreach ($this->resolveTagExpressionMembers($operation, $result) as $member) {
            $memberTags = $this->responseCacheService->evaluateStoreTagExpressions(
                $memberTagExpressions,
                $operation,
                $route,
                $request,
                ['object' => $member]
            );
            if ($memberTags === null) {
                return null;
            }

            $tags = [...$tags, ...$memberTags];
        }

        return array_values(array_unique($tags));
    }

    /**
     * Resolves the top-level result into the `AbstractDomainObject` instances
     * `memberTagExpressions` evaluates against: a collection GET's members (via
     * `AbstractCollectionResponse::getMembers()`), or an item GET's single result entity. Any
     * member that is not an `AbstractDomainObject` - a resource whose `entity` is not one, or a
     * future non-entity collection member - is skipped with a logged warning rather than evaluated
     * against.
     *
     * @return AbstractDomainObject[]
     */
    private function resolveTagExpressionMembers(OperationInterface $operation, mixed $result): array
    {
        $candidates = $result instanceof AbstractCollectionResponse ? $result->getMembers() : [$result];

        $members = [];
        foreach ($candidates as $candidate) {
            if (!$candidate instanceof AbstractDomainObject) {
                $this->logger?->warning(
                    't3api response cache memberTagExpressions skipped a result entry that is not an AbstractDomainObject',
                    ['operation' => $operation->getKey(), 'type' => get_debug_type($candidate)]
                );

                continue;
            }

            $members[] = $candidate;
        }

        return $members;
    }

    /**
     * Flushes the operation's declarative `cacheInvalidation` tags once a non-GET operation has
     * run to completion without throwing - a null response (no cacheable content, e.g. a DELETE)
     * or a 2xx status both count as success; anything else (a handler-reported error) skips the
     * flush. A `GET` operation never flushes, even when a resource-level `cacheInvalidation` block
     * cascades onto it - such a block is meant for the resource's write operations, and reading
     * never invalidates anything.
     *
     * `$result` - the write operation's persisted entity, or `null` on a `DELETE` (or when a
     * custom handler returns nothing) - is exposed to `tagExpressions` as `object` (fail-soft, like
     * every other part of this evaluation, see `ResponseCacheService::evaluateFlushTagExpressions()`).
     */
    private function flushInvalidationTagsAfterSuccessfulWrite(
        OperationInterface $operation,
        array $route,
        Request $request,
        ?ResponseInterface &$response,
        mixed $result = null
    ): void {
        if ($operation->isMethodGet() || !$this->isSuccessfulResponse($response)) {
            return;
        }

        $cacheInvalidationSettings = $operation->getCacheInvalidationSettings();
        $tagExpressions = $cacheInvalidationSettings->getTagExpressions();
        $expressionTags = $tagExpressions === []
            ? []
            : $this->responseCacheService->evaluateFlushTagExpressions(
                $tagExpressions,
                $operation,
                $route,
                $request,
                ['object' => $result]
            );

        $tags = array_values(array_unique([
            ...$this->filterValidTags($cacheInvalidationSettings->getTags()),
            ...$expressionTags,
        ]));
        if ($tags === []) {
            return;
        }

        $this->responseCacheService->flushByTags($tags);
        $this->responseCacheDebugHeaders->flushed($response, $tags);
    }

    private function isSuccessfulResponse(?ResponseInterface $response): bool
    {
        if ($response === null) {
            return true;
        }

        $statusCode = $response->getStatusCode();

        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Drops any literal tag the cache framework's allowed tag charset rejects - such a tag would
     * make `FrontendInterface::set()`/`flushByTags()` throw, taking the whole request down with
     * it, so it is skipped with a warning instead.
     *
     * @param string[] $tags
     * @return string[]
     */
    private function filterValidTags(array $tags): array
    {
        $validTags = [];
        foreach ($tags as $tag) {
            if (!$this->responseCacheService->isValidTag($tag)) {
                $this->logger?->warning('t3api response cache tag is invalid - skipped', ['tag' => $tag]);

                continue;
            }

            $validTags[] = $tag;
        }

        return array_values(array_unique($validTags));
    }

    /**
     * Seeds the collector with three tags before serialization runs, so a stored response always
     * carries them - even an empty collection, which `CacheTagSubscriber` never sees a single
     * entity for to derive a tag from. The bare `<table>` tag is a blanket lever: it is never the
     * automatic flush target of a membership-changing write (see `FlushCacheAfterSaveListener`),
     * but stays available for a manual/administrative "flush everything for this table" call, and
     * is exactly what `t3api:cache:invalidate-expired` needs, since it can only ever detect that a
     * table crossed a starttime/endtime threshold, never which uids did. The scope tag -
     * `<table>--collection` on a collection response, `<table>--single` on an item response - is
     * the precise automatic target for those writes, so invalidating a collection's membership
     * never evicts an unrelated, still-correct cached item response on the same table. The `--`
     * separator (rather than `_`, used by `<table>_<uid>`) is deliberate: a real TYPO3/Extbase
     * table name never contains a hyphen, so `collection`/`single` as a table-name suffix could in
     * principle collide with a genuine, unrelated table (e.g. `tx_shop_products_collection`) if `_`
     * were used instead. Resources not backed by an `AbstractDomainObject` (e.g. future DTO
     * resources) have no table to tag and are left TTL-only, consistent with the documented
     * content-based tagging semantics.
     */
    private function seedResourceTableTag(OperationInterface $operation): void
    {
        $entityClass = $operation->getApiResource()->getEntity();
        if (!is_subclass_of($entityClass, AbstractDomainObject::class)) {
            return;
        }

        $table = $this->dataMapper->getDataMap($entityClass)->getTableName();
        $scopeTag = $operation instanceof CollectionOperation ? $table . '--collection' : $table . '--single';
        $this->cacheTagCollector->addTags($table, $scopeTag);
    }

    /**
     * Evaluates the operation `security` expression on the cache path, before any cache
     * read or write - a cache hit would otherwise skip the access check the operation
     * handler normally performs. Returns the processor's output when the check itself
     * determines the request must fall through to the normal (uncached) handler path
     * instead of being gated here, or `null` when the caller may proceed with the
     * regular cache read/miss flow.
     *
     * @param callable(): string $processOperation
     * @throws OperationNotAllowedException
     */
    protected function enforceOperationSecurity(
        OperationInterface $operation,
        array $route,
        Request $request,
        callable $processOperation,
        ?ResponseInterface &$response
    ): ?string {
        if ($operation->getSecurity() === '') {
            return null;
        }

        if ($operation instanceof CollectionOperation) {
            if (!$this->operationAccessChecker->isGranted($operation)) {
                throw new OperationNotAllowedException($operation, 1574416639472);
            }

            return null;
        }

        return $this->enforceItemOperationSecurity($operation, $route, $request, $processOperation, $response);
    }

    /**
     * Item operation `security` expressions may reference the loaded entity via the
     * `object` variable, which the collection path never has to provide. Evaluation is
     * first attempted without it (the common case, e.g. checks against the current
     * user); only when that fails is the entity loaded - mirroring
     * `AbstractItemOperationHandler` - and the check retried with `object` in scope.
     *
     * @param callable(): string $processOperation
     * @throws OperationNotAllowedException
     */
    protected function enforceItemOperationSecurity(
        OperationInterface $operation,
        array $route,
        Request $request,
        callable $processOperation,
        ?ResponseInterface &$response
    ): ?string {
        try {
            $granted = $this->operationAccessChecker->isGranted($operation);
        } catch (\Throwable $throwable) {
            $this->logger?->warning(
                sprintf(
                    'Response cache security check for operation `%s` (`%s`) could not be evaluated without ' .
                    'the loaded `%s` entity - the `security` expression references the request-scoped `object` ' .
                    'variable, so the response cache path now loads the entity on every request to evaluate it. ' .
                    'This is a performance cost; consider avoiding `object` access in `security` for cached item operations.',
                    $operation->getKey(),
                    $operation->getPath(),
                    $operation->getApiResource()->getEntity()
                ),
                ['exception' => $throwable]
            );

            $object = $this->loadItemOperationObject($operation, $route);
            if ($object === null) {
                // Falls through to the normal handler without ever reading the cache - reported as
                // `miss` rather than left undecorated, since this response is still not served from
                // a stored entry.
                $this->responseCacheDebugHeaders->lookupOutcome($response, 'miss');

                return $this->processAndInvalidateCache($operation, $route, $request, $processOperation, $response);
            }

            $granted = $this->operationAccessChecker->isGranted($operation, ['object' => $object]);
        }

        if (!$granted) {
            throw new OperationNotAllowedException($operation, 1574411504130);
        }

        return null;
    }

    /**
     * Mirrors `AbstractItemOperationHandler::handle()`'s entity retrieval.
     */
    protected function loadItemOperationObject(OperationInterface $operation, array $route): ?AbstractDomainObject
    {
        $object = CommonRepository::getInstanceForOperation($operation)->findByUid((int)$route['id']);

        return $object instanceof AbstractDomainObject ? $object : null;
    }
}
