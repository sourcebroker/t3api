<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class RouteService
 */
class RouteService implements SingletonInterface
{
    /**
     * @return string
     */
    public static function getApiBasePath(): string
    {
        if (version_compare(TYPO3_branch, '9.5', '<')) {
            return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['basePath'];
        }

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

    /**
     * @return array
     */
    protected static function getApiRouteEnhancer(): array
    {
        static $apiRouteEnhancer;

        if (!empty($apiRouteEnhancer)) {
            return $apiRouteEnhancer;
        }

        foreach ((self::getSite()->getConfiguration()['routeEnhancers'] ?? []) as $routeEnhancer) {
            if ($routeEnhancer['type'] === ResourceEnhancer::ENHANCER_NAME) {
                $apiRouteEnhancer = $routeEnhancer;

                return $apiRouteEnhancer;
            }
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

    /**
     * @return string
     */
    protected static function getDefaultLanguageBasePath(): string
    {
        if (version_compare(TYPO3_branch, '9.4', '<')) {
            return '/';
        }

        return self::getSite()->getDefaultLanguage()->getBase()->getPath();
    }

    /**
     * @return Site
     */
    protected static function getSite(): Site
    {
        static $site;

        if (!empty($site)) {
            return $site;
        }

        if ($GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface
            && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site) {
            $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        } else {
            // fallback for backend requests (swagger module)
            $allSites = GeneralUtility::makeInstance(ObjectManager::class)->get(SiteFinder::class)->getAllSites();
            $siteFallback = null;
            foreach ($allSites as $siteToCheck) {
                $base = trim((string)$siteToCheck->getBase());
                if ($base === '/') {
                    $siteFallback = $siteToCheck;
                }
                if (rtrim($base, '/') === GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST')) {
                    $site = $siteToCheck;
                    break;
                }
            }
            if (empty($site)) {
                $site = $siteFallback;
            }
        }
        return $site;
    }
}
