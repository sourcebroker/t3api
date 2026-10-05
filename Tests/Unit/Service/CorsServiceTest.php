<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Service\CorsService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CorsServiceTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors'] = [
            'allowOrigin' => ['https://client.example'],
            'allowCredentials' => true,
            'allowMethods' => ['GET', 'POST'],
            'allowHeaders' => ['Content-Type', 'X-Token'],
            'exposeHeaders' => ['X-Total-Count'],
            'maxAge' => 600,
        ];
    }

    #[Test]
    #[DataProvider('actualRequestProvider')]
    public function actualResponsesFollowOriginPolicy(array $configuration, ?string $origin, bool $allowed): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors'] = array_replace(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors'],
            $configuration
        );
        $request = Request::create('https://api.example/items');
        if ($origin !== null) {
            $request->headers->set('Origin', $origin);
        }
        $response = (new CorsService())->applyActualResponse($request, new Response());

        self::assertSame($allowed ? $origin : '', $response->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertSame($allowed ? 'true' : '', $response->getHeaderLine('Access-Control-Allow-Credentials'));
        self::assertSame($allowed ? 'x-total-count' : '', $response->getHeaderLine('Access-Control-Expose-Headers'));
        self::assertSame('Origin', $response->getHeaderLine('Vary'));
        self::assertSame(200, $response->getStatusCode());
    }

    public static function actualRequestProvider(): array
    {
        return [
            'allowed origin' => [[], 'https://client.example', true],
            'disallowed origin' => [[], 'https://untrusted.example', false],
            'missing origin' => [[], null, false],
            'empty origin' => [[], '', false],
            'same origin' => [[], 'https://api.example', false],
            'opaque origin denied by default' => [[], 'null', false],
            'CORS disabled' => [['allowOrigin' => []], 'https://client.example', false],
            'wildcard string' => [['allowOrigin' => '*'], 'https://other.example', true],
            'wildcard array' => [['allowOrigin' => ['*']], 'https://other.example', true],
            'anchored regex match' => [
                ['originRegex' => true, 'allowOrigin' => ['^https://[a-z]+\\.example$']],
                'https://client.example', true,
            ],
            'anchored regex mismatch' => [
                ['originRegex' => true, 'allowOrigin' => ['^https://[a-z]+\\.example$']],
                'https://client.example.attacker.test', false,
            ],
            'legacy unanchored regex match' => [
                ['originRegex' => true, 'allowOrigin' => ['http://.*\\.example\\.com']],
                'http://client.example.com', true,
            ],
            'legacy unanchored regex rejects appended domain' => [
                ['originRegex' => true, 'allowOrigin' => ['http://.*\\.example\\.com']],
                'http://client.example.com.attacker.test', false,
            ],
            'regex alternative match' => [
                ['originRegex' => true, 'allowOrigin' => ['https://client\\.example|https://second\\.example']],
                'https://second.example', true,
            ],
            'first regex alternative rejects appended domain' => [
                ['originRegex' => true, 'allowOrigin' => ['https://client\\.example|https://second\\.example']],
                'https://client.example.attacker.test', false,
            ],
            'second regex alternative rejects appended domain' => [
                ['originRegex' => true, 'allowOrigin' => ['https://client\\.example|https://second\\.example']],
                'https://second.example.attacker.test', false,
            ],
            'regex must match the complete origin including scheme' => [
                ['originRegex' => true, 'allowOrigin' => ['client\\.example']],
                'https://client.example', false,
            ],
        ];
    }

    #[Test]
    public function disallowedOriginRemovesExistingCorsHeadersWithoutChangingResponse(): void
    {
        $request = Request::create('https://api.example/items');
        $request->headers->set('Origin', 'https://untrusted.example');
        $response = new Response('php://temp', 201, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Expose-Headers' => 'X-Secret',
            'Access-Control-Allow-Methods' => 'POST',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Max-Age' => '600',
            'Content-Type' => 'application/json',
        ]);
        $response->getBody()->write('{"created":true}');
        $response = (new CorsService())->applyActualResponse($request, $response);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('{"created":true}', (string)$response->getBody());
        self::assertSame(['Content-Type' => ['application/json'], 'Vary' => ['Origin']], $response->getHeaders());
    }

    #[Test]
    public function disabledCredentialsAreNotAdvertised(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowCredentials'] = false;
        $request = Request::create('https://api.example/items');
        $request->headers->set('Origin', 'https://client.example');
        $response = (new CorsService())->applyActualResponse(
            $request,
            new Response('php://temp', 200, ['Access-Control-Allow-Credentials' => 'true'])
        );

        self::assertSame('https://client.example', $response->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
    }

    #[Test]
    #[DataProvider('varyProvider')]
    public function varyPreservesExistingValues(string $existing, string $expected): void
    {
        $response = (new CorsService())->applyActualResponse(
            Request::create('https://api.example/items'),
            new Response('php://temp', 200, ['Vary' => $existing])
        );

        self::assertSame($expected, $response->getHeaderLine('Vary'));
    }

    public static function varyProvider(): array
    {
        return [
            'append' => ['Accept-Encoding', 'Accept-Encoding, Origin'],
            'already present in different case' => ['Accept-Encoding, origin', 'Accept-Encoding, origin'],
            'wildcard' => ['*', '*'],
        ];
    }

    #[Test]
    public function preflightValidatesAndNormalizesMethodAndHeaderLists(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowMethods'] = [' get ', 'post', 'POST'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowHeaders'] = [' Content-Type ', 'X-Token'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['simpleHeaders'] = ['X-Locale'];
        $request = $this->preflight();
        $request->headers->set('Access-Control-Request-Method', 'post');
        $request->headers->set('Access-Control-Request-Headers', "Content-Type , X-TOKEN,\tX-Locale");
        $response = (new CorsService())->applyPreflightResponse($request, new Response());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('https://client.example', $response->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertSame('true', $response->getHeaderLine('Access-Control-Allow-Credentials'));
        self::assertSame('GET, POST, post', $response->getHeaderLine('Access-Control-Allow-Methods'));
        self::assertSame('content-type, x-token, x-locale', $response->getHeaderLine('Access-Control-Allow-Headers'));
        self::assertSame('600', $response->getHeaderLine('Access-Control-Max-Age'));
        self::assertSame('Origin, Access-Control-Request-Method, Access-Control-Request-Headers', $response->getHeaderLine('Vary'));
        self::assertFalse($response->hasHeader('Access-Control-Expose-Headers'));
    }

    #[Test]
    #[DataProvider('deniedPreflightProvider')]
    public function deniedPreflightsDoNotAdvertisePermission(string $header, string $value, int $status): void
    {
        $request = $this->preflight();
        $request->headers->set($header, $value);
        $response = (new CorsService())->applyPreflightResponse(
            $request,
            new Response('php://temp', 200, ['Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Credentials' => 'true'])
        );

        self::assertSame($status, $response->getStatusCode());
        self::assertSame(['Vary'], array_keys($response->getHeaders()));
    }

    public static function deniedPreflightProvider(): array
    {
        return [
            'origin' => ['Origin', 'https://untrusted.example', 403],
            'empty origin' => ['Origin', '', 403],
            'method' => ['Access-Control-Request-Method', 'DELETE', 405],
            'empty method' => ['Access-Control-Request-Method', '', 405],
            'header' => ['Access-Control-Request-Headers', 'Content-Type, X-Forbidden', 405],
        ];
    }

    #[Test]
    public function wildcardHeadersAreEchoedAsConcreteNamesForCredentialedRequests(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowHeaders'] = '*';
        $request = $this->preflight();
        $request->headers->set('Access-Control-Request-Headers', 'Authorization, X-Custom, authorization');
        $response = (new CorsService())->applyPreflightResponse($request, new Response());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('authorization, x-custom', $response->getHeaderLine('Access-Control-Allow-Headers'));
        self::assertSame('true', $response->getHeaderLine('Access-Control-Allow-Credentials'));
    }

    #[Test]
    public function wildcardWithoutRequestedHeadersDoesNotEmitEmptyHeader(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowHeaders'] = '*';
        $response = (new CorsService())->applyPreflightResponse($this->preflight(), new Response());

        self::assertSame(200, $response->getStatusCode());
        self::assertFalse($response->hasHeader('Access-Control-Allow-Headers'));
    }

    #[Test]
    #[DataProvider('optionalListEntriesProvider')]
    public function optionalListEntriesDoNotBreakCorsResponses(string $option, array $values, string $header, string $expected): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors'][$option] = $values;
        $service = new CorsService();
        $request = Request::create('https://api.example/items');
        $request->headers->set('Origin', 'https://client.example');
        $actual = $service->applyActualResponse($request, new Response());
        $preflight = $service->applyPreflightResponse($this->preflight(), new Response());

        self::assertSame(200, $actual->getStatusCode());
        self::assertSame(200, $preflight->getStatusCode());
        $response = $header === 'Access-Control-Expose-Headers' ? $actual : $preflight;
        self::assertSame($expected, $response->getHeaderLine($header));
    }

    public static function optionalListEntriesProvider(): array
    {
        return [
            'nullable allowed headers' => [
                'allowHeaders', [null, ' Content-Type ', 'content-type', '', '  '],
                'Access-Control-Allow-Headers', 'content-type',
            ],
            'nullable language header in simpleHeaders' => [
                'simpleHeaders', [null, ' X-Locale ', 'x-locale', '', '  '],
                'Access-Control-Allow-Headers', 'content-type, x-token, x-locale',
            ],
            'nullable allowed methods' => [
                'allowMethods', [null, ' post ', 'POST', '', '  '],
                'Access-Control-Allow-Methods', 'POST',
            ],
            'nullable exposed headers' => [
                'exposeHeaders', [null, ' X-Total-Count ', 'x-total-count', '', '  '],
                'Access-Control-Expose-Headers', 'x-total-count',
            ],
        ];
    }

    #[Test]
    #[DataProvider('maxAgeProvider')]
    public function maxAgeRemainsConfigurable(?int $maxAge, string $expected): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['maxAge'] = $maxAge;
        $response = (new CorsService())->applyPreflightResponse($this->preflight(), new Response());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame($expected, $response->getHeaderLine('Access-Control-Max-Age'));
        self::assertSame($maxAge !== null, $response->hasHeader('Access-Control-Max-Age'));
    }

    public static function maxAgeProvider(): array
    {
        return [
            'omit header' => [null, ''],
            'disable preflight caching' => [0, '0'],
            'explicit lifetime' => [600, '600'],
        ];
    }

    private function preflight(): Request
    {
        $request = Request::create('https://api.example/items', 'OPTIONS');
        $request->headers->set('Origin', 'https://client.example');
        $request->headers->set('Access-Control-Request-Method', 'POST');

        return $request;
    }
}
