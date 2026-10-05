<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Extbase sets creation and modification date only in the database row. Response of write operations
 * has to contain these dates mapped the same way as in response of subsequent GET.
 *
 * Fixture: `Article` has typed, not initialized `crdate` (nullable) and `tstamp` (not nullable) properties.
 */
class CommonDateFieldsDispatcherTest extends AbstractDispatcherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/articles.csv');
    }

    #[Test]
    public function postReturnsDatesStoredInDatabase(): void
    {
        $article = $this->dispatch('POST', 'https://example.com/_api/articles', ['title' => 'New article']);

        self::assertNotNull($article['crdate']);
        self::assertNotNull($article['tstamp']);
        $this->assertDatesAreSameAsInGetResponse($article);
    }

    #[Test]
    public function patchReturnsUpdatedModificationDate(): void
    {
        $tstampBeforeUpdate = $this->dispatch('GET', 'https://example.com/_api/articles/1')['tstamp'];
        $article = $this->dispatch('PATCH', 'https://example.com/_api/articles/1', ['title' => 'Changed title']);

        self::assertNotSame($tstampBeforeUpdate, $article['tstamp']);
        $this->assertDatesAreSameAsInGetResponse($article);
    }

    #[Test]
    public function patchReturnsZeroCreationDateMappedLikeExtbase(): void
    {
        $article = $this->dispatch('PATCH', 'https://example.com/_api/articles/1', ['title' => 'Changed title']);

        self::assertNull($article['crdate'] ?? null);
        $this->assertDatesAreSameAsInGetResponse($article);
    }

    private function assertDatesAreSameAsInGetResponse(array $article): void
    {
        // Drop objects held by persistence session, so GET maps the record from the database
        $this->get(PersistenceManagerInterface::class)->clearState();
        $storedArticle = $this->dispatch('GET', 'https://example.com/_api/articles/' . $article['uid']);

        self::assertSame($storedArticle['crdate'] ?? null, $article['crdate'] ?? null);
        self::assertSame($storedArticle['tstamp'] ?? null, $article['tstamp'] ?? null);
    }

    private function dispatch(string $method, string $url, ?array $body = null): array
    {
        $symfonyRequest = SymfonyRequest::create(
            $url,
            $method,
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR)
        );
        $response = new Response();

        return json_decode(
            $this->get(Bootstrap::class)->processOperationByRequest(
                (new RequestContext())->fromRequest($symfonyRequest),
                $symfonyRequest,
                $response
            ),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
