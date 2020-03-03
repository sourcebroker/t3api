<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Service;

use RuntimeException;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
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
            return '_api';
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
     * @throws SiteNotFoundException
     * @return Site
     */
    protected static function getSite(): Site
    {
        static $site;

        if (!empty($site)) {
            return $site;
        }

        /** @var Site $site */
        $site = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(SiteFinder::class)
            ->getSiteByIdentifier('main');

        return $site;
    }
}
