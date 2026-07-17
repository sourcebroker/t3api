<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Core\Environment;

/**
 * Decorates the response with `X-T3api-Cache*` debug headers describing what
 * `OperationResponseCache` did for the current request - hit/miss/bypass, the entry identifier,
 * the full stored tag set, and any tags a `cacheInvalidation` flush actually flushed. These
 * headers disclose cache internals, so they are hard-off outside
 * `Environment::getContext()->isDevelopment()` (see `isDevelopmentContext()`) - every method below
 * is a no-op, leaving `$response` untouched, both when the gate is closed and when `$response`
 * itself is `null` (a caller that omits it, mirroring `OperationResponseCache::resolve()`).
 */
class ResponseCacheDebugHeaders
{
    private const HEADER_CACHE = 'X-T3api-Cache';
    private const HEADER_CACHE_IDENTIFIER = 'X-T3api-Cache-Identifier';
    private const HEADER_CACHE_TAGS = 'X-T3api-Cache-Tags';
    private const HEADER_CACHE_FLUSHED_TAGS = 'X-T3api-Cache-Flushed-Tags';

    /**
     * A cache HIT: the response is about to be served straight from the stored entry.
     */
    public function hit(?ResponseInterface &$response, string $cacheEntryIdentifier): void
    {
        if (!$this->shouldDecorate($response)) {
            return;
        }

        $response = $response
            ->withHeader(self::HEADER_CACHE, 'hit')
            ->withHeader(self::HEADER_CACHE_IDENTIFIER, $cacheEntryIdentifier);
    }

    /**
     * A cache lookup that did not serve a HIT: `miss` (the lookup ran and found nothing) or
     * `bypass` (`readCondition` evaluated falsy, so the lookup never ran). Either way the response
     * may still end up stored - see `stored()`, called separately once that is known.
     */
    public function lookupOutcome(?ResponseInterface &$response, string $outcome): void
    {
        if (!$this->shouldDecorate($response)) {
            return;
        }

        $response = $response->withHeader(self::HEADER_CACHE, $outcome);
    }

    /**
     * The response was actually written to the cache - the entry identifier it was stored under,
     * and the full tag set it was stored with, in the same deterministic order it was stored in.
     *
     * @param string[] $tags
     */
    public function stored(?ResponseInterface &$response, string $cacheEntryIdentifier, array $tags): void
    {
        if (!$this->shouldDecorate($response)) {
            return;
        }

        $response = $response
            ->withHeader(self::HEADER_CACHE_IDENTIFIER, $cacheEntryIdentifier)
            ->withHeader(self::HEADER_CACHE_TAGS, implode(',', $tags));
    }

    /**
     * A `cacheInvalidation` flush actually ran - the tags it flushed. Omitted entirely when
     * `$tags` is empty, since a flush that flushed nothing is not worth surfacing.
     *
     * @param string[] $tags
     */
    public function flushed(?ResponseInterface &$response, array $tags): void
    {
        if ($tags === [] || !$this->shouldDecorate($response)) {
            return;
        }

        $response = $response->withHeader(self::HEADER_CACHE_FLUSHED_TAGS, implode(',', $tags));
    }

    private function shouldDecorate(?ResponseInterface $response): bool
    {
        return $response !== null && $this->isDevelopmentContext();
    }

    /**
     * Isolated behind its own method - not called inline from the four methods above - so a unit
     * test can stub the development gate by overriding this one method, without touching TYPO3's
     * global `Environment` state.
     */
    protected function isDevelopmentContext(): bool
    {
        return Environment::getContext()->isDevelopment();
    }
}
