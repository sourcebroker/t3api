<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use Throwable;

/**
 * Class T3apiRequestResolver
 */
class T3apiRequestResolver implements MiddlewareInterface
{
    private Bootstrap $bootstrap;

    public function __construct(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (is_array($request->getQueryParams())
            && array_key_exists(ResourceEnhancer::PARAMETER_NAME, $request->getQueryParams())) {
            return $this->bootstrap->process($this->cleanupRequest($request));
        }

        return $handler->handle($request);
    }

    /**
     * Removes `t3apiResource` query parameter as it may break further functionality.
     * This parameter is needed only to reach a handler - further processing should not rely on it.
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    private function cleanupRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $cleanedQueryParams = $request->getQueryParams();
        unset($cleanedQueryParams[ResourceEnhancer::PARAMETER_NAME]);

        return $request->withQueryParams($cleanedQueryParams);
    }
}
