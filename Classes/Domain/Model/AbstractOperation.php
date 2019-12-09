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
     * @var string
     */
    protected $security = '';

    /**
     * @var string
     */
    protected $securityPostDenormalize = '';

    /**
     * AbstractOperation constructor.
     *
     * @param string $key
     * @param ApiResource $apiResource
     * @param array $params
     */
    public function __construct(string $key, ApiResource $apiResource, array $params)
    {
        $this->key = $key;
        $this->apiResource = $apiResource;
        $this->method = strtoupper($params['method'] ?? $this->method);
        $this->path = $params['path'] ?? $this->path;
        $this->security = $params['security'] ?? $this->security;
        $this->securityPostDenormalize = $params['security_post_denormalize'] ?? $this->securityPostDenormalize;
        $this->normalizationContext = isset($params['normalizationContext'])
            ? array_replace_recursive($this->normalizationContext, $params['normalizationContext'])
            : $this->normalizationContext;
        $this->route = new Route(
            RouteService::getFullApiBasePath() . $this->path,
            [],
            [],
            [],
            null,
            [],
            [$this->method]
        );
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
     * @return string
     */
    public function getSecurity(): string
    {
        return $this->security;
    }

    /**
     * @return string
     */
    public function getSecurityPostDenormalize(): string
    {
        return $this->securityPostDenormalize;
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

    /**
     * @return bool
     */
    public function isMethodGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * @return bool
     */
    public function isMethodPut(): bool
    {
        return $this->method === 'PUT';
    }

    /**
     * @return bool
     */
    public function isMethodPatch(): bool
    {
        return $this->method === 'Patch';
    }

    /**
     * @return bool
     */
    public function isMethodPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * @return bool
     */
    public function isMethodDelete(): bool
    {
        return $this->method === 'DELETE';
    }
}
