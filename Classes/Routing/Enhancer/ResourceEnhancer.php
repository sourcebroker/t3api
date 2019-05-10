<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Routing\Enhancer;

use TYPO3\CMS\Core\Routing\Enhancer\AbstractEnhancer;
use TYPO3\CMS\Core\Routing\Enhancer\RoutingEnhancerInterface;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Core\Routing\RouteCollection;

/**
 * routeEnhancers:
 *   Restify:
 *     type: RestifyResourceEnhancer
 *     basePath: '_api'
 *
 * @todo configurable `basePath` is not working yet
 */
class ResourceEnhancer extends AbstractEnhancer implements RoutingEnhancerInterface
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var string
     * @todo set empty string when RESTIFY_BASE_PATH is finally removed
     */
    protected $basePath = RESTIFY_BASE_PATH;

    /**
     * ResourceEnhancer constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
        // @todo uncomment when RESTIFY_BASE_PATH is finally removed
//        $this->basePath = $this->configuration['basePath'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function enhanceForMatching(RouteCollection $collection): void
    {
        /** @var Route $variant */
        $variant = clone $collection->get('default');
        $variant->setPath(trim($this->basePath, '/').'/{resource?}');
        $variant->setRequirement('resource', '.*');
        $variant->setOption(
            '_decoratedParameters',
            ['type' => 1557300854213]
        );
        $collection->add('enhancer_'.$this->basePath.spl_object_hash($variant), $variant);
    }

    /**
     * {@inheritdoc}
     * // @todo Think if it ever could be needed
     */
    public function enhanceForGeneration(RouteCollection $collection, array $parameters): void
    {
    }
}
