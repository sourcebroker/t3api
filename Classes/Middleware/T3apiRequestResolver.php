<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use SourceBroker\T3api\Service\RequestLanguageService;
use SourceBroker\T3api\Service\RouteService;

class T3apiRequestResolver implements MiddlewareInterface
{
    protected Bootstrap $bootstrap;

    public function __construct(
        Bootstrap $bootstrap,
        protected RequestLanguageService $requestLanguageService
    ) {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (RouteService::routeHasT3ApiResourceEnhancerQueryParam($request)) {
            // Required for TYPO3 13+ (TYPO3 12 requires T3apiRequestLanguageResolver)
            $request = $this->requestLanguageService->withLanguageFromHeader($request);

            // @todo Refactor RouteService to receive the current request explicitly (DI or an
            //       explicit parameter) instead of reading $GLOBALS['TYPO3_REQUEST'], so this
            //       superglobal dependency can be removed entirely.
            $GLOBALS['TYPO3_REQUEST'] = $request;

            return $this->bootstrap->process($this->cleanupRequest($request));
        }

        return $handler->handle($request);
    }

    /**
     * Removes `t3apiResource` query parameter as it may break further functionality.
     * This parameter is needed only to reach a handler - further processing should not rely on it.
     */
    private function cleanupRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $cleanedQueryParams = $request->getQueryParams();
        unset($cleanedQueryParams[ResourceEnhancer::PARAMETER_NAME]);

        return $request->withQueryParams($cleanedQueryParams);
    }
}
