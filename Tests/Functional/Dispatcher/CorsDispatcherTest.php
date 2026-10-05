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
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class CorsDispatcherTest extends AbstractDispatcherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors'] = [
            'allowOrigin' => ['https://client.example', 'https://second.example'],
            'allowCredentials' => true,
            'allowMethods' => ['GET', 'POST'],
            'allowHeaders' => ['Content-Type'],
            'maxAge' => 600,
        ];
    }

    #[Test]
    #[DataProvider('optionsProvider')]
    public function optionsRequestsDistinguishPreflightFromOrdinaryOptions(
        array $headers,
        int $status,
        string $allowedOrigin,
        bool $preflightAllowed
    ): void {
        $response = $this->dispatch('OPTIONS', '/_api/articles', $headers);

        self::assertSame($status, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame('', (string)$response->getBody());
        self::assertSame($allowedOrigin, $response->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertSame($allowedOrigin !== '' ? 'true' : '', $response->getHeaderLine('Access-Control-Allow-Credentials'));
        self::assertSame($preflightAllowed ? 'GET, POST' : '', $response->getHeaderLine('Access-Control-Allow-Methods'));
        self::assertSame($preflightAllowed ? 'content-type' : '', $response->getHeaderLine('Access-Control-Allow-Headers'));
        self::assertSame('Origin, Access-Control-Request-Method, Access-Control-Request-Headers', $response->getHeaderLine('Vary'));
        self::assertSame(0, $this->getConnectionPool()->getConnectionForTable('tx_functionaltest_domain_model_article')
            ->count('*', 'tx_functionaltest_domain_model_article', []));
    }

    public static function optionsProvider(): array
    {
        $preflight = ['Origin' => 'https://client.example', 'Access-Control-Request-Method' => 'POST'];

        return [
            'plain OPTIONS' => [[], 200, '', false],
            'OPTIONS with Origin only' => [['Origin' => 'https://client.example'], 200, 'https://client.example', false],
            'OPTIONS without Origin' => [['Access-Control-Request-Method' => 'POST'], 200, '', false],
            'valid preflight' => [$preflight + ['Access-Control-Request-Headers' => 'Content-Type'], 200, 'https://client.example', true],
            'denied origin' => [array_replace($preflight, ['Origin' => 'https://untrusted.example']), 403, '', false],
            'denied method' => [array_replace($preflight, ['Access-Control-Request-Method' => 'DELETE']), 405, '', false],
            'denied header' => [$preflight + ['Access-Control-Request-Headers' => 'X-Forbidden'], 405, '', false],
        ];
    }

    #[Test]
    public function cachedResponseUsesOriginOfCurrentRequest(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/books.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/authors.csv');
        $initial = $this->dispatch('GET', '/_api/books');
        self::assertSame(200, $initial->getStatusCode(), (string)$initial->getBody());
        self::assertStringContainsString('First book', (string)$initial->getBody());
        self::assertFalse($initial->hasHeader('Access-Control-Allow-Origin'));
        self::assertSame('Origin', $initial->getHeaderLine('Vary'));

        // Prove later requests use cached content, while their CORS policy is evaluated afresh.
        $this->getConnectionPool()->getConnectionForTable('tx_functionaltest_domain_model_book')->update(
            'tx_functionaltest_domain_model_book',
            ['title' => 'Changed after warming cache'],
            ['uid' => 1]
        );
        $this->get(PersistenceManagerInterface::class)->clearState();

        foreach (['https://client.example', 'https://second.example', 'https://untrusted.example'] as $origin) {
            $response = $this->dispatch('GET', '/_api/books', ['Origin' => $origin]);
            self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
            self::assertSame((string)$initial->getBody(), (string)$response->getBody());
            self::assertSame(
                $origin === 'https://untrusted.example' ? '' : $origin,
                $response->getHeaderLine('Access-Control-Allow-Origin')
            );
            self::assertSame(
                $origin === 'https://untrusted.example' ? '' : 'true',
                $response->getHeaderLine('Access-Control-Allow-Credentials')
            );
            self::assertSame('Origin', $response->getHeaderLine('Vary'));
        }
    }

    #[Test]
    public function errorResponsesRemainReadableByAllowedOrigins(): void
    {
        $response = $this->dispatch('GET', '/_api/no-such-resource', ['Origin' => 'https://client.example']);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('https://client.example', $response->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertSame('true', $response->getHeaderLine('Access-Control-Allow-Credentials'));
        self::assertSame('Origin', $response->getHeaderLine('Vary'));
    }

    #[Test]
    public function legacyOriginRegexDoesNotGrantPreflightToAppendedDomain(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['originRegex'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowOrigin'] = ['http://.*\\.example\\.com'];
        $allowed = $this->dispatch('OPTIONS', '/_api/articles', [
            'Origin' => 'http://client.example.com',
            'Access-Control-Request-Method' => 'POST',
        ]);
        self::assertSame(200, $allowed->getStatusCode());
        self::assertSame('http://client.example.com', $allowed->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertSame('true', $allowed->getHeaderLine('Access-Control-Allow-Credentials'));

        $denied = $this->dispatch('OPTIONS', '/_api/articles', [
            'Origin' => 'http://client.example.com.attacker.test',
            'Access-Control-Request-Method' => 'POST',
        ]);
        self::assertSame(403, $denied->getStatusCode());
        self::assertFalse($denied->hasHeader('Access-Control-Allow-Origin'));
        self::assertFalse($denied->hasHeader('Access-Control-Allow-Credentials'));
    }

    private function dispatch(string $method, string $path, array $headers = []): ResponseInterface
    {
        // Each call models a separate HTTP request, with a fresh response body.
        $dispatcher = new Bootstrap(
            $this->get(SerializerService::class),
            $this->get(ApiResourceRepository::class),
            $this->get(SerializationContextBuilder::class),
            $this->get(DeserializationContextBuilder::class),
            $this->get(EventDispatcherInterface::class),
            $this->get(OperationResponseCache::class)
        );
        $request = new ServerRequest('https://example.com' . $path, $method);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $dispatcher->process($request);
    }
}
