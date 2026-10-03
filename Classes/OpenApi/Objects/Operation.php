<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Exceptions\InvalidArgumentException;
use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property string|null $action
 * @property string[]|null $tags
 * @property string|null $summary
 * @property string|null $description
 * @property \SourceBroker\T3api\OpenApi\Objects\ExternalDocs|null $externalDocs
 * @property string|null $operationId
 * @property \SourceBroker\T3api\OpenApi\Objects\Parameter[]|null $parameters
 * @property \SourceBroker\T3api\OpenApi\Objects\RequestBody|null $requestBody
 * @property \SourceBroker\T3api\OpenApi\Objects\Response[]|null $responses
 * @property bool|null $deprecated
 * @property \SourceBroker\T3api\OpenApi\Objects\SecurityRequirement[]|null $security
 * @property bool|null $noSecurity
 * @property \SourceBroker\T3api\OpenApi\Objects\Server[]|null $servers
 */
class Operation extends BaseObject
{
    public const ACTION_GET = 'get';
    public const ACTION_PUT = 'put';
    public const ACTION_POST = 'post';
    public const ACTION_DELETE = 'delete';
    public const ACTION_OPTIONS = 'options';
    public const ACTION_HEAD = 'head';
    public const ACTION_PATCH = 'patch';
    public const ACTION_TRACE = 'trace';

    /**
     * @var string|null
     */
    protected $action;

    /**
     * @var string[]|null
     */
    protected $tags;

    /**
     * @var string|null
     */
    protected $summary;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\ExternalDocs|null
     */
    protected $externalDocs;

    /**
     * @var string|null
     */
    protected $operationId;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Parameter[]|null
     */
    protected $parameters;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\RequestBody|null
     */
    protected $requestBody;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Response[]|null
     */
    protected $responses;

    /**
     * @var bool|null
     */
    protected $deprecated;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\SecurityRequirement[]|null
     */
    protected $security;

    /**
     * @var bool|null
     */
    protected $noSecurity;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Server[]|null
     */
    protected $servers;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\PathItem[]|null
     */
    protected $callbacks;

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function get(?string $objectId = null): self
    {
        return static::create($objectId)->action(static::ACTION_GET);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function put(?string $objectId = null): self
    {
        return static::create($objectId)->action(static::ACTION_PUT);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function post(?string $objectId = null): self
    {
        return static::create($objectId)->action(static::ACTION_POST);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function delete(?string $objectId = null): self
    {
        return static::create($objectId)->action(static::ACTION_DELETE);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function head(?string $objectId = null): self
    {
        return static::create($objectId)->action(static::ACTION_HEAD);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function patch(?string $objectId = null): self
    {
        return static::create($objectId)->action(static::ACTION_PATCH);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function trace(?string $objectId = null): self
    {
        return static::create($objectId)->action(static::ACTION_TRACE);
    }

    /**
     * @param string|null $action
     * @return static
     */
    public function action(?string $action): self
    {
        $instance = clone $this;

        $instance->action = $action;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Tag|string ...$tags
     * @throws \SourceBroker\T3api\OpenApi\Exceptions\InvalidArgumentException
     * @return static
     */
    public function tags(...$tags): self
    {
        // Only allow Tag instances and strings.
        foreach ($tags as &$tag) {
            // If a Tag instance was passed in then extract it's name string.
            if ($tag instanceof Tag) {
                $tag = $tag->name;
                continue;
            }

            if (is_string($tag)) {
                continue;
            }

            throw new InvalidArgumentException(
                sprintf(
                    'The tags must either be a string or an instance of [%s].',
                    Tag::class
                )
            );
        }
        unset($tag);

        $instance = clone $this;

        $instance->tags = $tags ?: null;

        return $instance;
    }

    /**
     * @param string|null $summary
     * @return static
     */
    public function summary(?string $summary): self
    {
        $instance = clone $this;

        $instance->summary = $summary;

        return $instance;
    }

    /**
     * @param string|null $description
     * @return static
     */
    public function description(?string $description): self
    {
        $instance = clone $this;

        $instance->description = $description;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\ExternalDocs|null $externalDocs
     * @return static
     */
    public function externalDocs(?ExternalDocs $externalDocs): self
    {
        $instance = clone $this;

        $instance->externalDocs = $externalDocs;

        return $instance;
    }

    /**
     * @param string|null $operationId
     * @return static
     */
    public function operationId(?string $operationId): self
    {
        $instance = clone $this;

        $instance->operationId = $operationId;

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
     * @param \SourceBroker\T3api\OpenApi\Objects\RequestBody|null $requestBody
     * @return static
     */
    public function requestBody(?RequestBody $requestBody): self
    {
        $instance = clone $this;

        $instance->requestBody = $requestBody;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Response ...$responses
     * @return static
     */
    public function responses(Response ...$responses): self
    {
        $instance = clone $this;

        $instance->responses = $responses;

        return $instance;
    }

    /**
     * @param bool|null $deprecated
     * @return static
     */
    public function deprecated(?bool $deprecated = true): self
    {
        $instance = clone $this;

        $instance->deprecated = $deprecated;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\SecurityRequirement ...$security
     * @return static
     */
    public function security(SecurityRequirement ...$security): self
    {
        $instance = clone $this;

        $instance->security = $security ?: null;
        $instance->noSecurity = null;

        return $instance;
    }

    /**
     * @param bool|null $noSecurity
     * @return static
     */
    public function noSecurity(?bool $noSecurity = true): self
    {
        $instance = clone $this;

        $instance->noSecurity = $noSecurity;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Server ...$servers
     * @return static
     */
    public function servers(Server ...$servers): self
    {
        $instance = clone $this;

        $instance->servers = $servers ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\PathItem ...$callbacks
     * @return $this
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
        $responses = [];
        foreach ($this->responses ?? [] as $response) {
            $responses[$response->statusCode ?? 'default'] = $response;
        }

        $callbacks = [];
        foreach ($this->callbacks ?? [] as $callback) {
            $callbacks[$callback->objectId ?? ''][$callback->route ?? ''] = $callback;
        }

        return Arr::filter([
            'tags' => $this->tags,
            'summary' => $this->summary,
            'description' => $this->description,
            'externalDocs' => $this->externalDocs,
            'operationId' => $this->operationId,
            'parameters' => $this->parameters,
            'requestBody' => $this->requestBody,
            'responses' => $responses ?: null,
            'deprecated' => $this->deprecated,
            'security' => $this->noSecurity ? [] : $this->security,
            'servers' => $this->servers,
            'callbacks' => $callbacks ?: null,
        ]);
    }
}
