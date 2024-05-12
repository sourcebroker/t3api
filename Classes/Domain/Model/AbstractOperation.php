<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Service\RouteService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

abstract class AbstractOperation implements OperationInterface
{
    protected string $key;

    protected ApiResource $apiResource;

    protected string $method = 'GET';

    protected string $path = '/';

    protected Route $route;

    protected ?array $normalizationContext = null;

    protected ?array $denormalizationContext = null;

    protected string $security = '';

    protected string $securityPostDenormalize = '';

    protected PersistenceSettings $persistenceSettings;

    protected UploadSettings $uploadSettings;

    public function __construct(string $key, ApiResource $apiResource, array $params)
    {
        $this->key = $key;
        $this->apiResource = $apiResource;
        $this->method = strtoupper($params['method'] ?? $this->method);
        $this->path = $params['path'] ?? $this->path;
        $this->security = $params['security'] ?? $this->security;
        $this->securityPostDenormalize = $params['security_post_denormalize'] ?? $this->securityPostDenormalize;
        $this->normalizationContext = isset($params['normalizationContext'])
            ? array_replace_recursive([], $params['normalizationContext'])
            : $this->normalizationContext;
        $this->denormalizationContext = isset($params['denormalizationContext'])
            ? array_replace_recursive([], $params['denormalizationContext'])
            : $this->denormalizationContext;
        $this->route = new Route(
            RouteService::getFullApiBasePath() . $this->path,
            [],
            [],
            [],
            null,
            [],
            [$this->method, Request::METHOD_OPTIONS]
        );
        $this->persistenceSettings = PersistenceSettings::create(
            $params['attributes']['persistence'] ?? [],
            $apiResource->getPersistenceSettings()
        );
        $this->uploadSettings = UploadSettings::create(
            $params['attributes']['upload'] ?? [],
            $apiResource->getUploadSettings()
        );
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSecurity(): string
    {
        return $this->security;
    }

    public function getSecurityPostDenormalize(): string
    {
        return $this->securityPostDenormalize;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    public function getApiResource(): ApiResource
    {
        return $this->apiResource;
    }

    public function getNormalizationContext(): ?array
    {
        return $this->normalizationContext;
    }

    public function getDenormalizationContext(): ?array
    {
        return $this->denormalizationContext;
    }

    public function isMethodGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isMethodPut(): bool
    {
        return $this->method === 'PUT';
    }

    public function isMethodPatch(): bool
    {
        return $this->method === 'PATCH';
    }

    public function isMethodPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isMethodDelete(): bool
    {
        return $this->method === 'DELETE';
    }

    public function getPersistenceSettings(): PersistenceSettings
    {
        return $this->persistenceSettings;
    }

    public function getUploadSettings(): UploadSettings
    {
        return $this->uploadSettings;
    }
}
