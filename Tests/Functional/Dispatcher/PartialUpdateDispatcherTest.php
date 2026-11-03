<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Http\Response;

/**
 * PATCH changes only the properties sent in the request body - properties missing in the body keep their values,
 * also when the property declares a default value (`protected string $title = ''`).
 */
class PartialUpdateDispatcherTest extends AbstractDispatcherTestCase
{
    private const ARTICLE_TABLE = 'tx_functionaltest_domain_model_article';

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/articles.csv');
    }

    #[Test]
    public function patchKeepsPropertiesMissingInRequestBody(): void
    {
        $article = $this->dispatchPatch('{}');

        self::assertSame('Article without creation date', $article['title']);
        self::assertSame('Article without creation date', $this->fetchTitle());
    }

    #[Test]
    public function patchChangesPropertiesSentInRequestBody(): void
    {
        $article = $this->dispatchPatch('{"title": "Changed title"}');

        self::assertSame('Changed title', $article['title']);
        self::assertSame('Changed title', $this->fetchTitle());
    }

    private function fetchTitle(): string
    {
        return (string)$this->getConnectionPool()->getConnectionForTable(self::ARTICLE_TABLE)
            ->select(['title'], self::ARTICLE_TABLE, ['uid' => 1])->fetchOne();
    }

    private function dispatchPatch(string $body): array
    {
        $symfonyRequest = SymfonyRequest::create(
            'https://example.com/_api/articles/1',
            'PATCH',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
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
