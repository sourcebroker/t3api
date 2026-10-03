<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Contracts\SchemaContract;
use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property \SourceBroker\T3api\OpenApi\Objects\Schema[]|null $schemas
 * @property \SourceBroker\T3api\OpenApi\Objects\Response[]|null $responses
 * @property \SourceBroker\T3api\OpenApi\Objects\Parameter[]|null $parameters
 * @property \SourceBroker\T3api\OpenApi\Objects\Example[]|null $examples
 * @property \SourceBroker\T3api\OpenApi\Objects\RequestBody[]|null $requestBodies
 * @property \SourceBroker\T3api\OpenApi\Objects\Header[]|null $headers
 * @property \SourceBroker\T3api\OpenApi\Objects\SecurityScheme[]|null $securitySchemes
 * @property \SourceBroker\T3api\OpenApi\Objects\Link[]|null $links
 */
class Components extends BaseObject
{
    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Schema[]|null
     */
    protected $schemas;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Response[]|null
     */
    protected $responses;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Parameter[]|null
     */
    protected $parameters;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Example[]|null
     */
    protected $examples;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\RequestBody[]|null
     */
    protected $requestBodies;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Header[]|null
     */
    protected $headers;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\SecurityScheme[]|null
     */
    protected $securitySchemes;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Link[]|null
     */
    protected $links;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\PathItem[]|null
     */
    protected $callbacks;

    /**
     * @param \SourceBroker\T3api\OpenApi\Contracts\SchemaContract ...$schemas
     * @return static
     */
    public function schemas(SchemaContract ...$schemas): self
    {
        $instance = clone $this;

        $instance->schemas = $schemas ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Response ...$responses
     * @return static
     */
    public function responses(Response ...$responses): self
    {
        $instance = clone $this;

        $instance->responses = $responses ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Parameter ...$parameters
     * @return static
     */
    public function parameters(Parameter ...$parameters): self
    {
        $instance = clone $this;

        $instance->parameters = $parameters ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Example ...$examples
     * @return static
     */
    public function examples(Example ...$examples): self
    {
        $instance = clone $this;

        $instance->examples = $examples ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\RequestBody ...$requestBodies
     * @return static
     */
    public function requestBodies(RequestBody ...$requestBodies): self
    {
        $instance = clone $this;

        $instance->requestBodies = $requestBodies ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Header ...$headers
     * @return static
     */
    public function headers(Header ...$headers): self
    {
        $instance = clone $this;

        $instance->headers = $headers ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\SecurityScheme ...$securitySchemes
     * @return static
     */
    public function securitySchemes(SecurityScheme ...$securitySchemes): self
    {
        $instance = clone $this;

        $instance->securitySchemes = $securitySchemes ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Link ...$links
     * @return static
     */
    public function links(Link ...$links): self
    {
        $instance = clone $this;

        $instance->links = $links ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\PathItem ...$callbacks
     * @return static
     */
    public function callbacks(PathItem ...$callbacks): self
    {
        $instance = clone $this;

        $instance->callbacks = $callbacks ?: null;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        $schemas = [];
        foreach ($this->schemas ?? [] as $schema) {
            $schemas[$schema->objectId ?? ''] = $schema;
        }

        $responses = [];
        foreach ($this->responses ?? [] as $response) {
            $responses[$response->objectId ?? ''] = $response;
        }

        $parameters = [];
        foreach ($this->parameters ?? [] as $parameter) {
            $parameters[$parameter->objectId ?? ''] = $parameter;
        }

        $examples = [];
        foreach ($this->examples ?? [] as $example) {
            $examples[$example->objectId ?? ''] = $example;
        }

        $requestBodies = [];
        foreach ($this->requestBodies ?? [] as $requestBodie) {
            $requestBodies[$requestBodie->objectId ?? ''] = $requestBodie;
        }

        $headers = [];
        foreach ($this->headers ?? [] as $header) {
            $headers[$header->objectId ?? ''] = $header;
        }

        $securitySchemes = [];
        foreach ($this->securitySchemes ?? [] as $securityScheme) {
            $securitySchemes[$securityScheme->objectId ?? ''] = $securityScheme;
        }

        $links = [];
        foreach ($this->links ?? [] as $link) {
            $links[$link->objectId ?? ''] = $link;
        }

        $callbacks = [];
        foreach ($this->callbacks ?? [] as $callback) {
            $callbacks[$callback->objectId ?? ''][$callback->route ?? ''] = $callback;
        }

        return Arr::filter([
            'schemas' => $schemas ?: null,
            'responses' => $responses ?: null,
            'parameters' => $parameters ?: null,
            'examples' => $examples ?: null,
            'requestBodies' => $requestBodies ?: null,
            'headers' => $headers ?: null,
            'securitySchemes' => $securitySchemes ?: null,
            'links' => $links ?: null,
            'callbacks' => $callbacks ?: null,
        ]);
    }
}
