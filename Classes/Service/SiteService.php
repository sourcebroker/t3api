<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteService
{
    public static function getCurrent(): Site
    {
        static $site;

        if ($site === null) {
            $site = self::getResolvedByTypo3() ?? self::getMatchingCurrentUrl();
        }

        if (!$site instanceof Site) {
            throw new RuntimeException('Could not determine current site', 1604259480589);
        }

        return $site;
    }

    public static function getAll(): array
    {
        static $allSites;

        return $allSites ??
            $allSites = GeneralUtility::makeInstance(SiteFinder::class)
                ->getAllSites();
    }

    public static function hasT3apiRouteEnhancer(Site $site): bool
    {
        return !empty(self::getT3apiRouteEnhancer($site));
    }

    public static function getT3apiRouteEnhancer(Site $site): ?array
    {
        foreach ($site->getConfiguration()['routeEnhancers'] ?? [] as $routeEnhancer) {
            if ($routeEnhancer['type'] === ResourceEnhancer::ENHANCER_NAME) {
                return $routeEnhancer;
            }
        }

        return null;
    }

    /**
     * @param string $identifier
     * @return Site
     * @throws SiteNotFoundException
     */
    public static function getByIdentifier(string $identifier): Site
    {
        return GeneralUtility::makeInstance(SiteFinder::class)
            ->getSiteByIdentifier($identifier);
    }

    protected static function getResolvedByTypo3(): ?Site
    {
        return $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface
        && $GLOBALS['TYPO3_REQUEST']->getAttribute('site') instanceof Site
            ? $GLOBALS['TYPO3_REQUEST']->getAttribute('site') : null;
    }

    protected static function getMatchingCurrentUrl(): ?Site
    {
        foreach (self::getAll() as $site) {
            if (rtrim(trim((string)$site->getBase()), '/')
                === GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST')) {
                return $site;
            }
        }

        return null;
    }
}
