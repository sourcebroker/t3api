<?php

namespace SourceBroker\T3api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\T3api\Service\RouteService;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class PrepareT3ApiRequest implements MiddlewareInterface
{
    /**
     * Prepare Language switch for t3api
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        /** @var SiteLanguage $language */
        $language = $request->getAttribute('language');
        $t3apiHeaderLanguage = $this->getT3apiLanguage($request);
        $languageUid = $t3apiHeaderLanguage ?: 0;

        if (($this->isT3apiRequest($request) || $t3apiHeaderLanguage !== null) &&
            (($language && $language->getLanguageId() != $languageUid) || ($language === null))
        ) {
            $request = $this->prepareRequest($request, $t3apiHeaderLanguage);
        }
        return $handler->handle($request);
    }

    protected function isT3apiRequest(ServerRequestInterface $request): bool
    {
        $apiBasePath = '/' . RouteService::getApiBasePath();
        $path = $request->getUri()->getPath();
        return strpos($path, $apiBasePath) === 0;
    }

    protected function getT3apiLanguage(ServerRequestInterface $request):? int
    {
        $languageHeader = $request->getHeader($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader']);
        return (!empty($languageHeader) ? (int)array_shift($languageHeader) : null);
    }

    protected function prepareRequest(ServerRequestInterface $request, $languageUid): ServerRequestInterface
    {
        /** @var Site $site */
        $site = $request->getAttribute('site');
        $newLanguage = $site->getLanguageById($languageUid ?: 0);
        $request = $request->withAttribute('language', $newLanguage);
        $request = $request->withAttribute('t3apiLanguageUid', $languageUid);

        /** @var SiteRouteResult $previousResult */
        $previousResult = $request->getAttribute('routing', null);
        $request = $request->withAttribute('routing', new SiteRouteResult(
            $previousResult->getUri(),
            $previousResult->getSite(),
            $newLanguage,
            $previousResult->getTail()
        ));
        return $request;
    }
}
diff --git a/Configuration/RequestMiddlewares.php b/Configuration/RequestMiddlewares.php
