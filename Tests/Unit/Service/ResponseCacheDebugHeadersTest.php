<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Service\ResponseCacheDebugHeaders;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * The `Environment::getContext()->isDevelopment()` gate is stubbed via a protected-method
 * override on an anonymous subclass (see `createDebugHeaders()`) rather than manipulating
 * TYPO3's global `Environment` state - the same testability pattern already used for
 * `OperationResponseCache::loadItemOperationObject()`.
 */
class ResponseCacheDebugHeadersTest extends UnitTestCase
{
    #[Test]
    public function gateClosedLeavesHitResponseUnchanged(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: false);
        $response = new Response();

        $debugHeaders->hit($response, 'entry-id');

        self::assertSame([], $response->getHeaders());
    }

    #[Test]
    public function gateClosedLeavesLookupOutcomeResponseUnchanged(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: false);
        $response = new Response();

        $debugHeaders->lookupOutcome($response, 'miss');

        self::assertSame([], $response->getHeaders());
    }

    #[Test]
    public function gateClosedLeavesStoredResponseUnchanged(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: false);
        $response = new Response();

        $debugHeaders->stored($response, 'entry-id', ['tag-a']);

        self::assertSame([], $response->getHeaders());
    }

    #[Test]
    public function gateClosedLeavesFlushedResponseUnchanged(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: false);
        $response = new Response();

        $debugHeaders->flushed($response, ['tag-a']);

        self::assertSame([], $response->getHeaders());
    }

    #[Test]
    public function hitSetsCacheAndIdentifierHeaders(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: true);
        $response = new Response();

        $debugHeaders->hit($response, 'entry-id');

        self::assertSame('hit', $response->getHeaderLine('X-T3api-Cache'));
        self::assertSame('entry-id', $response->getHeaderLine('X-T3api-Cache-Identifier'));
    }

    #[Test]
    public function lookupOutcomeSetsCacheHeaderToGivenOutcome(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: true);
        $response = new Response();

        $debugHeaders->lookupOutcome($response, 'bypass');

        self::assertSame('bypass', $response->getHeaderLine('X-T3api-Cache'));
        self::assertFalse($response->hasHeader('X-T3api-Cache-Identifier'));
    }

    #[Test]
    public function storedSetsIdentifierAndTagsHeaders(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: true);
        $response = new Response();

        $debugHeaders->stored($response, 'entry-id', ['tag-a', 'tag-b']);

        self::assertSame('entry-id', $response->getHeaderLine('X-T3api-Cache-Identifier'));
        self::assertSame('tag-a,tag-b', $response->getHeaderLine('X-T3api-Cache-Tags'));
    }

    #[Test]
    public function flushedSetsFlushedTagsHeader(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: true);
        $response = new Response();

        $debugHeaders->flushed($response, ['tag-a', 'tag-b']);

        self::assertSame('tag-a,tag-b', $response->getHeaderLine('X-T3api-Cache-Flushed-Tags'));
    }

    #[Test]
    public function flushedOmitsHeaderWhenTagsAreEmpty(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: true);
        $response = new Response();

        $debugHeaders->flushed($response, []);

        self::assertFalse($response->hasHeader('X-T3api-Cache-Flushed-Tags'));
    }

    #[Test]
    public function nullResponseIsANoOpForEveryMethod(): void
    {
        $debugHeaders = $this->createDebugHeaders(isDevelopment: true);
        $response = null;

        $debugHeaders->hit($response, 'entry-id');
        $debugHeaders->lookupOutcome($response, 'miss');
        $debugHeaders->stored($response, 'entry-id', ['tag-a']);
        $debugHeaders->flushed($response, ['tag-a']);

        self::assertNull($response);
    }

    private function createDebugHeaders(bool $isDevelopment): ResponseCacheDebugHeaders
    {
        return new class ($isDevelopment) extends ResponseCacheDebugHeaders {
            public function __construct(private readonly bool $isDevelopment) {}

            protected function isDevelopmentContext(): bool
            {
                return $this->isDevelopment;
            }
        };
    }
}
