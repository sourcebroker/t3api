<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ServerRequestInterface;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class RouteService implements SingletonInterface
{
    public static function getApiBasePath(): string
    {
        return trim(
            self::getApiRouteEnhancer()['basePath'] ?? $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['basePath'],
            '/'
        );
    }

    /**
     * Returns base path including language prefix
     */
    public static function getFullApiBasePath(): string
    {
        return trim(self::getLanguageBasePath() . self::getApiBasePath(), '/');
    }

    public static function getFullApiBaseUrl(): string
    {
        return rtrim((string)SiteService::getCurrent()->getBase(), '/')
            . '/' . ltrim(self::getFullApiBasePath(), '/');
    }

    public static function routeHasT3ApiResourceEnhancerQueryParam(?ServerRequestInterface $request = null): bool
    {
        $request = $request ?? self::getRequest();
        return $request instanceof ServerRequest && is_array($request->getQueryParams())
            && array_key_exists(ResourceEnhancer::PARAMETER_NAME, $request->getQueryParams());
    }

    /**
     * Returns the absolute (site-base-included) API path for a given site language,
     * e.g. "/shop/de/_api" for a "de" language based at "/de/" on a site based at "/shop/".
     */
    public static function getApiPathForLanguage(SiteLanguage $language): string
    {
        return rtrim('/' . trim($language->getBase()->getPath(), '/'), '/') . '/' . self::getApiBasePath();
    }

    /**
     * Strips a site's base path off the front of $path, if present.
     *
     * Needed because SiteLanguage::getBase() resolves to an absolute path that already
     * includes the site's own base (e.g. site base "/shop/" + language base "/de/" =>
     * "/shop/de/"), while getFullApiBaseUrl() prepends the site base separately - so the
     * language prefix used there must not also carry it, or the site base gets duplicated.
     */
    public static function stripSiteBasePathPrefix(string $path, string $siteBasePath): string
    {
        if ($siteBasePath !== '/' && str_starts_with($path, $siteBasePath)) {
            return '/' . ltrim(substr($path, strlen($siteBasePath)), '/');
        }

        return $path;
    }

    protected static function getApiRouteEnhancer(): array
    {
        static $apiRouteEnhancer;

        if (!empty($apiRouteEnhancer)) {
            return $apiRouteEnhancer;
        }

        $routeEnhancer = SiteService::getT3apiRouteEnhancer(SiteService::getCurrent());

        if ($routeEnhancer !== null) {
            return $routeEnhancer;
        }

        throw new \RuntimeException(
            sprintf(
                'Route enhancer `%s` is not defined. You need to add it to your site configuration first. See example configuration in PHP doc of %s.',
                ResourceEnhancer::ENHANCER_NAME,
                ResourceEnhancer::class
            ),
            1565853631761
        );
    }

    /**
     * We support for two cases:language set in X-Locale header
     * 1) when request has X-Locale header with language (t3apiHeaderLanguageRequest))
     * 2) when request has no X-Locale header and url itself stores language information
     */
    protected static function getLanguageBasePath(): string
    {
        $request = self::getRequest();
        /** @var SiteLanguage $requestLanguage */
        $requestLanguage = $request?->getAttribute('language');
        if ($requestLanguage instanceof SiteLanguage
            && $request?->getAttribute('t3apiHeaderLanguageRequest') !== true) {
            $languagePrefix = $requestLanguage->getBase()->getPath();
        } else {
            $languagePrefix = SiteService::getCurrent()->getDefaultLanguage()->getBase()->getPath();
        }

        $requestPath = $request?->getUri()->getPath() ?? '';
        if (!str_starts_with($requestPath, $languagePrefix)) {
            return '';
        }

        return self::stripSiteBasePathPrefix($languagePrefix, SiteService::getCurrent()->getBase()->getPath());
    }

    protected static function getRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
