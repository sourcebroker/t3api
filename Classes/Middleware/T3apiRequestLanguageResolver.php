<?php

namespace SourceBroker\T3api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\T3api\Service\RouteService;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class T3apiRequestLanguageResolver implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var SiteLanguage $language */
        $language = $request->getAttribute('language');
        $t3apiHeaderLanguageUid = $this->getT3apiLanguageUid($request);

        if ($t3apiHeaderLanguageUid !== null
            && RouteService::routeHasT3ApiResourceEnhancerQueryParam($request)
            && ($language instanceof SiteLanguage && $language->getLanguageId() !== $t3apiHeaderLanguageUid)
        ) {
            $request->withAttribute('t3apiHeaderLanguageRequest', true);
            $request = $request->withAttribute(
                'language',
                $request->getAttribute('site')->getLanguageById($t3apiHeaderLanguageUid)
            );
        }
        return $handler->handle($request);
    }

    protected function getT3apiLanguageUid(ServerRequestInterface $request): ?int
    {
        $languageHeader = $request->getHeader($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader']);
        return !empty($languageHeader) ? (int)array_shift($languageHeader) : null;
    }
}
