<?php

namespace SourceBroker\Restify\Domain\Model;

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
     * AbstractOperation constructor.
     *
     * @param string $key
     * @param array $params
     */
    public function __construct(string $key, ApiResource $apiResource, array $params)
    {
        $this->key = $key;
        $this->apiResource = $apiResource;
        $this->method = $params['method'] ?? $this->method;
        $this->path = $params['path'] ?? $this->path;
        // @todo base path should be read from route enhancer configuration when RESTIFY_BASE_PATH is finally removed
        $this->route = new Route(rtrim(RESTIFY_BASE_PATH, '/') . $this->path);
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

}
