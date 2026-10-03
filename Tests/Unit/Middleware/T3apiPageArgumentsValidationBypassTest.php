<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\T3api\Middleware\T3apiPageArgumentsRestorer;
use SourceBroker\T3api\Middleware\T3apiPageArgumentsValidationBypass;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class T3apiPageArgumentsValidationBypassTest extends UnitTestCase
{
    #[Test]
    public function queryArgumentsOfApiRequestAreHiddenFromValidation(): void
    {
        $pageArguments = new PageArguments(1, '0', ['t3apiResource' => 'news/news'], [], ['istopnews' => '1', 'page' => '2']);

        $request = $this->process(new T3apiPageArgumentsValidationBypass(), $this->createRequest($pageArguments));

        $validatedPageArguments = $request->getAttribute('routing');
        self::assertInstanceOf(PageArguments::class, $validatedPageArguments);
        self::assertSame([], $validatedPageArguments->getDynamicArguments());
        self::assertSame(1, $validatedPageArguments->getPageId());
        self::assertSame($pageArguments, $request->getAttribute(T3apiPageArgumentsValidationBypass::ORIGINAL_ROUTING_ATTRIBUTE));
    }

    #[Test]
    public function nonApiRequestIsNotModified(): void
    {
        $pageArguments = new PageArguments(1, '0', [], [], ['foo' => 'bar']);
        $request = $this->createRequest($pageArguments);

        $processedRequest = $this->process(new T3apiPageArgumentsValidationBypass(), $request);

        self::assertSame($request, $processedRequest);
    }

    #[Test]
    public function restorerPutsOriginalPageArgumentsBack(): void
    {
        $pageArguments = new PageArguments(1, '0', ['t3apiResource' => 'news/news'], [], ['istopnews' => '1']);
        $request = $this->process(new T3apiPageArgumentsValidationBypass(), $this->createRequest($pageArguments));

        $restoredRequest = $this->process(new T3apiPageArgumentsRestorer(), $request);

        self::assertSame($pageArguments, $restoredRequest->getAttribute('routing'));
        self::assertNull($restoredRequest->getAttribute(T3apiPageArgumentsValidationBypass::ORIGINAL_ROUTING_ATTRIBUTE));
    }

    #[Test]
    public function restorerDoesNotModifyRequestWithoutOriginalPageArguments(): void
    {
        $request = $this->createRequest(new PageArguments(1, '0', [], [], ['foo' => 'bar']));

        self::assertSame($request, $this->process(new T3apiPageArgumentsRestorer(), $request));
    }

    private function createRequest(PageArguments $pageArguments): ServerRequestInterface
    {
        return (new ServerRequest('https://example.com/_api/news/news'))->withAttribute('routing', $pageArguments);
    }

    /**
     * Runs the middleware and returns the request it passed to the next handler.
     */
    private function process(
        T3apiPageArgumentsValidationBypass|T3apiPageArgumentsRestorer $middleware,
        ServerRequestInterface $request
    ): ServerRequestInterface {
        $handler = new class () implements RequestHandlerInterface {
            public ?ServerRequestInterface $request = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->request = $request;

                return new Response();
            }
        };

        $middleware->process($request, $handler);

        self::assertInstanceOf(ServerRequestInterface::class, $handler->request);

        return $handler->request;
    }
}
