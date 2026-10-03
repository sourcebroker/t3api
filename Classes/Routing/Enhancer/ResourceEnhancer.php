<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Routing\Enhancer;

use SourceBroker\T3api\Service\RouteService;
use TYPO3\CMS\Core\Routing\Enhancer\AbstractEnhancer;
use TYPO3\CMS\Core\Routing\Enhancer\RoutingEnhancerInterface;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Core\Routing\RouteCollection;

/**
 * routeEnhancers:
 *   T3api:
 *     type: T3apiResourceEnhancer
 */
class ResourceEnhancer extends AbstractEnhancer implements RoutingEnhancerInterface
{
    public const ENHANCER_NAME = 'T3apiResourceEnhancer';
    public const PARAMETER_NAME = 't3apiResource';

    /**
     * Codes of exceptions thrown by SiteService::getCurrent() ("Could not determine current site")
     * and RouteService::getApiRouteEnhancer() ("Route enhancer is not defined")
     */
    protected const SKIPPABLE_BASE_PATH_EXCEPTION_CODES = [1604259480589, 1565853631761];

    protected array $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function enhanceForMatching(RouteCollection $collection): void
    {
        try {
            $basePath = $this->getBasePath();
        } catch (\RuntimeException $exception) {
            // The T3api base path cannot be resolved when the current site is not determinable
            // (e.g. CLI requests without request URL) or has no T3api route enhancer. The API
            // routes are irrelevant for such requests, so skip enhancement instead of breaking
            // route matching for the whole request. Other exceptions indicate real errors.
            if (!in_array($exception->getCode(), self::SKIPPABLE_BASE_PATH_EXCEPTION_CODES, true)) {
                throw $exception;
            }
            return;
        }

        /** @var Route $variant */
        $variant = clone $collection->get('default');
        $variant->setPath($basePath . sprintf('/{%s?}', self::PARAMETER_NAME));
        $variant->setRequirement(self::PARAMETER_NAME, '.*');
        $collection->add('enhancer_' . $basePath . spl_object_hash($variant), $variant);
    }

    /**
     * {@inheritdoc}
     * // @todo Think if it ever could be needed
     */
    public function enhanceForGeneration(RouteCollection $collection, array $parameters): void {}

    protected function getBasePath(): string
    {
        static $basePath;

        return $basePath ?? $basePath = RouteService::getApiBasePath();
    }
}
