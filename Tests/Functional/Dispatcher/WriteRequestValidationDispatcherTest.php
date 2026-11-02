<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Service\OperationResponseCache;
use SourceBroker\T3api\Service\SerializerService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Stream;

/**
 * Requests changing data have to come from an origin allowed by the CORS configuration (or without `Origin` header)
 * and a JSON body has to be sent with a JSON content type.
 */
class WriteRequestValidationDispatcherTest extends AbstractDispatcherTestCase
{
    private const ARTICLE_TABLE = 'tx_functionaltest_domain_model_article';

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowOrigin'] = ['https://client.example'];
    }

    #[Test]
    #[DataProvider('rejectedOriginProvider')]
    public function writeRequestFromNotAllowedOriginIsRejected(string $origin): void
    {
        $response = $this->dispatch('POST', '/_api/articles', '{"title": "Rejected"}', ['Origin' => $origin]);

        $json = $this->assertError($response, 403, 'Origin not allowed');
        self::assertSame(sprintf('Origin `%s` is not allowed to send `POST` requests', $origin), $json['hydra:description']);
        self::assertFalse($response->hasHeader('Access-Control-Allow-Origin'));
        self::assertSame(0, $this->countArticles());
    }

    public static function rejectedOriginProvider(): array
    {
        return [
            'other origin' => ['https://untrusted.example'],
            'other scheme of own host' => ['http://example.com'],
            'opaque origin' => ['null'],
        ];
    }

    #[Test]
    #[DataProvider('acceptedOriginProvider')]
    public function writeRequestFromAllowedOriginOrWithoutOriginIsExecuted(array $headers): void
    {
        $response = $this->dispatch('POST', '/_api/articles', '{"title": "Created"}', $headers);

        self::assertSame(201, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(1, $this->countArticles());
    }

    public static function acceptedOriginProvider(): array
    {
        return [
            'without origin' => [[]],
            'same origin' => [['Origin' => 'https://example.com']],
            'same origin behind reverse proxy' => [['Origin' => 'https://example.com', 'Host' => 'backend.internal:8080']],
            'allowed origin' => [['Origin' => 'https://client.example']],
        ];
    }

    #[Test]
    public function wildcardOriginAllowsWriteRequestFromAnyOrigin(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowOrigin'] = '*';

        $response = $this->dispatch('POST', '/_api/articles', '{"title": "Created"}', ['Origin' => 'https://untrusted.example']);

        self::assertSame(201, $response->getStatusCode(), (string)$response->getBody());
    }

    #[Test]
    public function patchRequestFromNotAllowedOriginIsRejected(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/articles.csv');

        $response = $this->dispatch('PATCH', '/_api/articles/1', '{"title": "Rejected"}', ['Origin' => 'https://untrusted.example']);

        $this->assertError($response, 403, 'Origin not allowed');
        self::assertSame('Article without creation date', $this->getConnectionPool()->getConnectionForTable(self::ARTICLE_TABLE)
            ->select(['title'], self::ARTICLE_TABLE, ['uid' => 1])->fetchOne());
    }

    #[Test]
    public function readRequestFromNotAllowedOriginIsNotRejected(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/articles.csv');

        $response = $this->dispatch('GET', '/_api/articles/1', '', ['Origin' => 'https://untrusted.example'], null);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertFalse($response->hasHeader('Access-Control-Allow-Origin'));
    }

    #[Test]
    #[DataProvider('unsupportedContentTypeProvider')]
    public function jsonBodyWithUnsupportedContentTypeIsRejected(?string $contentType, string $description): void
    {
        $response = $this->dispatch('POST', '/_api/articles', '{"title": "Rejected"}', [], $contentType);

        $json = $this->assertError($response, 415, 'Unsupported media type');
        self::assertSame($description, $json['hydra:description']);
        self::assertSame(0, $this->countArticles());
    }

    public static function unsupportedContentTypeProvider(): array
    {
        return [
            'text/plain' => ['text/plain', 'Content type `text/plain` is not supported, use `application/json`'],
            'form' => ['application/x-www-form-urlencoded', 'Content type `application/x-www-form-urlencoded` is not supported, use `application/json`'],
            'missing' => [null, 'Header `Content-Type` is required, use `application/json`'],
        ];
    }

    #[Test]
    #[DataProvider('supportedContentTypeProvider')]
    public function jsonBodyWithJsonContentTypeIsAccepted(string $contentType): void
    {
        $response = $this->dispatch('POST', '/_api/articles', '{"title": "Created"}', [], $contentType);

        self::assertSame(201, $response->getStatusCode(), (string)$response->getBody());
    }

    public static function supportedContentTypeProvider(): array
    {
        return [
            'application/json' => ['application/json'],
            'with charset' => ['application/json; charset=utf-8'],
            'uppercase' => ['Application/JSON'],
            'JSON-LD' => ['application/ld+json'],
            'JSON merge patch' => ['application/merge-patch+json'],
        ];
    }

    private function assertError(ResponseInterface $response, int $status, string $title): array
    {
        $body = (string)$response->getBody();
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($status, $response->getStatusCode(), $body);
        self::assertSame($title, $json['hydra:title']);

        return $json;
    }

    private function countArticles(): int
    {
        return $this->getConnectionPool()->getConnectionForTable(self::ARTICLE_TABLE)->count('*', self::ARTICLE_TABLE, []);
    }

    private function dispatch(
        string $method,
        string $path,
        string $body,
        array $headers,
        ?string $contentType = 'application/json'
    ): ResponseInterface {
        $stream = new Stream('php://temp', 'rw');
        $stream->write($body);
        $stream->rewind();

        $request = new ServerRequest('https://example.com' . $path, $method, $stream);
        if ($contentType !== null) {
            $request = $request->withHeader('Content-Type', $contentType);
        }
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $dispatcher = new Bootstrap(
            $this->get(SerializerService::class),
            $this->get(ApiResourceRepository::class),
            $this->get(SerializationContextBuilder::class),
            $this->get(DeserializationContextBuilder::class),
            $this->get(EventDispatcherInterface::class),
            $this->get(OperationResponseCache::class)
        );

        return $dispatcher->process($request);
    }
}
