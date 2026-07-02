<?php

namespace SourceBroker\T3api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\T3api\Service\RequestLanguageService;
use SourceBroker\T3api\Service\RouteService;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class T3apiRequestLanguageResolver implements MiddlewareInterface
{
    public function __construct(protected RequestLanguageService $requestLanguageService) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($this->isT3apiRequest($request)) {
            /**
             * Only registered for TYPO3 v12 (see Configuration/RequestMiddlewares.php). On v13+,
             * T3apiRequestResolver's own header override, applied later, is sufficient on its own -
             * remove this class entirely once TYPO3 v12 support is dropped.
             */
            $request = $this->requestLanguageService->withLanguageFromHeader($request);
        }

        return $handler->handle($request);
    }

    private function isT3apiRequest(ServerRequestInterface $request): bool
    {
        if (RouteService::routeHasT3ApiResourceEnhancerQueryParam($request)) {
            return true;
        }

        $language = $request->getAttribute('language');
        if (!$language instanceof SiteLanguage) {
            return false;
        }

        $requestPath = '/' . trim($request->getUri()->getPath(), '/');
        $apiPath = RouteService::getApiPathForLanguage($language);

        return $requestPath === $apiPath || str_starts_with($requestPath, $apiPath . '/');
    }
}
