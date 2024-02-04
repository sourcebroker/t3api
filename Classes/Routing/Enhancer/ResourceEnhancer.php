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
     * @var array
     */
    protected $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function enhanceForMatching(RouteCollection $collection): void
    {
        /** @var Route $variant */
        $variant = clone $collection->get('default');
        $variant->setPath($this->getBasePath() . sprintf('/{%s?}', self::PARAMETER_NAME));
        $variant->setRequirement(self::PARAMETER_NAME, '.*');
        $collection->add('enhancer_' . $this->getBasePath() . spl_object_hash($variant), $variant);
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
