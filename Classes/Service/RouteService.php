<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use RuntimeException;

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
        return self::getApiRouteEnhancer()['basePath'];
    }

    /**
     * @return array
     *
     * Throwing an exception here have no big sense because if route enhancer is not defined this code probably will
     *    never be executed, but lets assume someone can call it from outside t3api context.
     */
    protected static function getApiRouteEnhancer(): array
    {
        static $apiRouteEnhancer;

        if (!empty($apiRouteEnhancer)) {
            return $apiRouteEnhancer;
        }

        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        foreach (($site->getConfiguration()['routeEnhancers'] ?? []) as $routeEnhancer) {
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
}
