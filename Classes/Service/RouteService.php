<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Service;

use RuntimeException;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use TYPO3\CMS\Core\SingletonInterface;

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
     * @return string
     */
    public static function getFullApiBasePath(): string
    {
        return trim(self::getDefaultLanguageBasePath() . self::getApiBasePath(), '/');
    }

    public static function getFullApiBaseUrl(): string
    {
        return rtrim((string)SiteService::getCurrent()->getBase(), '/')
            . '/' . ltrim(self::getFullApiBasePath(), '/');
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

        throw new RuntimeException(
            sprintf(
                'Route enhancer `%s` is not defined. You need to add it to your site configuration first. See example configuration in PHP doc of %s.',
                ResourceEnhancer::ENHANCER_NAME,
                ResourceEnhancer::class
            ),
            1565853631761
        );
    }

    protected static function getDefaultLanguageBasePath(): string
    {
        return SiteService::getCurrent()->getDefaultLanguage()->getBase()->getPath();
    }
}
