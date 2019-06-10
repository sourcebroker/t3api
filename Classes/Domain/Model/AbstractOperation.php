<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Model;

use SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer;
use Symfony\Component\Routing\Route;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Class AbstractOperation
 */
abstract class AbstractOperation
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var ApiResource
     */
    protected $apiResource;

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $path = '/';

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var array
     */
    protected $normalizationContext = [];

    /**
     * AbstractOperation constructor.
     * @param string $key
     * @param ApiResource $apiResource
     * @param array $params
     *
     * @throws RouteNotFoundException
     */
    public function __construct(string $key, ApiResource $apiResource, array $params)
    {
        $this->key = $key;
        $this->apiResource = $apiResource;
        $this->method = $params['method'] ?? $this->method;
        $this->path = $params['path'] ?? $this->path;
        $this->normalizationContext = isset($params['normalizationContext'])
            ? array_replace_recursive($this->normalizationContext, $params['normalizationContext'])
            : $this->normalizationContext;
        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        $routeEnhancers = $site->getConfiguration()['routeEnhancers'] ?? [];
        foreach ($routeEnhancers as $routeEnhancer) {
            if ($routeEnhancer['type'] == ResourceEnhancer::ENHANCER_NAME && isset($routeEnhancer['basePath'])) {
                $this->route = new Route(rtrim($routeEnhancer['basePath'], '/') . $this->path);
                break;
            }
        }

        if (empty($this->route)) {
            throw new RouteNotFoundException('Route not found for restify extension', 1757217286469);
        }
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @return ApiResource
     */
    public function getApiResource(): ApiResource
    {
        return $this->apiResource;
    }

    /**
     * @return string[]
     */
    public function getContextGroups(): array
    {
        return $this->normalizationContext['groups'] ?? [];
    }
}
