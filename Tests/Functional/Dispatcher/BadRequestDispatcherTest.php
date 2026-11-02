<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Exception\BadRequestException;
use SourceBroker\T3api\Filter\DistanceFilter;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Service\OperationResponseCache;
use SourceBroker\T3api\Service\RequestLanguageService;
use SourceBroker\T3api\Service\SerializerService;
use T3apiTests\FunctionalTest\Domain\Model\Product;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Invalid data sent by the client results in HTTP 400 with a description, also outside of
 * `Development` context - unlike unexpected exceptions, see InternalErrorDispatcherTest.
 */
class BadRequestDispatcherTest extends AbstractDispatcherTestCase
{
    #[Test]
    #[DataProvider('invalidBodyProvider')]
    public function invalidRequestBodyIsRejected(string $body, string $description): void
    {
        $response = $this->dispatch($this->createRequest('POST', '/_api/articles', $body));

        $this->assertBadRequest($response, $description);
        self::assertSame(0, $this->getConnectionPool()->getConnectionForTable('tx_functionaltest_domain_model_article')
            ->count('*', 'tx_functionaltest_domain_model_article', []));
    }

    public static function invalidBodyProvider(): array
    {
        return [
            'malformed JSON' => ['{"title": ', 'Request body is not valid JSON: Syntax error'],
            'empty body' => ['', 'Request body is not valid JSON: Syntax error'],
            'JSON array' => ['[{"title": "Article"}]', 'Request body has to be a JSON object'],
            'JSON string' => ['"Article"', 'Request body has to be a JSON object'],
        ];
    }

    #[Test]
    #[DataProvider('invalidOrderProvider')]
    public function invalidOrderDirectionIsRejected(string $query, string $description): void
    {
        $response = $this->dispatch($this->createRequest('GET', '/_api/products?' . $query));

        $this->assertBadRequest($response, $description);
    }

    public static function invalidOrderProvider(): array
    {
        return [
            'unknown direction' => ['order[title]=sideways', 'Unknown order direction `sideways`, expected `asc` or `desc`'],
            'array instead of direction' => ['order[title][]=asc', 'Unknown order direction `array`, expected `asc` or `desc`'],
        ];
    }

    #[Test]
    public function unavailableLanguageInHeaderIsRejectedWithCorsHeaders(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowOrigin'] = ['https://client.example'];
        $request = $this->createRequest('GET', '/_api/products')
            ->withHeader('X-Locale', '99')
            ->withHeader('Origin', 'https://client.example');

        $response = $this->dispatch($this->get(RequestLanguageService::class)->withLanguageFromHeader($request));

        $this->assertBadRequest($response, 'Header `X-Locale` has to contain the ID of an available language, `99` given');
        self::assertSame('https://client.example', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    #[Test]
    #[DataProvider('invalidRelationProvider')]
    public function invalidRelationValueIsRejected(string $body, int $code, string $description): void
    {
        try {
            $this->get(SerializerService::class)->deserialize($body, Product::class);
            self::fail('BadRequestException expected');
        } catch (BadRequestException $exception) {
            self::assertSame($code, $exception->getCode());
            self::assertSame($description, $exception->getDescription());
        }
    }

    public static function invalidRelationProvider(): array
    {
        return [
            'single relation' => [
                '{"pid": 0, "category": true}',
                1584866997736,
                'Value of type `boolean` in path `category` has to be an identifier or an object',
            ],
            'collection relation' => [
                '{"pid": 0, "tags": "first"}',
                1570805126535,
                'Value of type `string` in path `tags` has to be an array',
            ],
        ];
    }

    #[Test]
    public function distanceFilterWithoutCoordinatesIsRejected(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionCode(1793641752);
        $this->expectExceptionMessage('Parameters `position[lat]` and `position[lng]` are required to use distance filter');

        GeneralUtility::makeInstance(DistanceFilter::class)->filterProperty(
            'position',
            ['lat' => '50.06'],
            self::createStub(QueryInterface::class),
            new ApiFilter(DistanceFilter::class, 'position', '', ['parameterName' => 'position'])
        );
    }

    private function assertBadRequest(ResponseInterface $response, string $description): void
    {
        $body = (string)$response->getBody();
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, $response->getStatusCode(), $body);
        self::assertSame('Bad request', $json['hydra:title']);
        self::assertSame($description, $json['hydra:description']);
    }

    private function createRequest(string $method, string $path, ?string $body = null): ServerRequest
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write($body ?? '');
        $stream->rewind();

        return (new ServerRequest('https://example.com' . $path, $method, $stream, ['Content-Type' => 'application/json']))
            ->withQueryParams($this->parseQuery($path))
            ->withAttribute('site', $GLOBALS['TYPO3_REQUEST']->getAttribute('site'))
            ->withAttribute('language', $GLOBALS['TYPO3_REQUEST']->getAttribute('language'));
    }

    private function parseQuery(string $path): array
    {
        parse_str((string)parse_url($path, PHP_URL_QUERY), $query);

        return $query;
    }

    private function dispatch(ServerRequestInterface $request): ResponseInterface
    {
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
