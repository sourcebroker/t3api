<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Service\RouteService;
use Symfony\Component\Routing\Route;

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
        $this->route = new Route(rtrim(RouteService::getApiBasePath(), '/') . $this->path);
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
