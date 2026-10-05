<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Processor;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Processor\CorsProcessor;
use SourceBroker\T3api\Service\CorsService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CorsProcessorTest extends UnitTestCase
{
    #[Test]
    public function subclassCanExcludeRequestsFromCorsProcessing(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors'] = ['allowOrigin' => ['https://client.example']];
        $processor = new class (new CorsService()) extends CorsProcessor {
            protected function isCorsRequest(Request $request): bool
            {
                return false;
            }
        };
        $request = Request::create('https://api.example/items');
        $request->headers->set('Origin', 'https://client.example');
        $response = new Response();
        $processor->process($request, $response);

        self::assertFalse($response->hasHeader('Access-Control-Allow-Origin'));
        self::assertSame('Origin', $response->getHeaderLine('Vary'));
    }

    #[Test]
    public function subclassCanIncludeSameOriginRequestsInCorsProcessing(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors'] = ['allowOrigin' => ['https://api.example']];
        $processor = new class (new CorsService()) extends CorsProcessor {
            protected function isCorsRequest(Request $request): bool
            {
                return true;
            }
        };
        $request = Request::create('https://api.example/items');
        $request->headers->set('Origin', 'https://api.example');
        $response = new Response();
        $processor->process($request, $response);

        self::assertSame('https://api.example', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    #[Test]
    public function missingServiceLeavesResponseUnchanged(): void
    {
        $response = new Response();
        $original = $response;
        (new CorsProcessor(null))->process(Request::create('https://api.example/items'), $response);

        self::assertSame($original, $response);
    }
}
