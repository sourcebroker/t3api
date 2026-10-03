<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Validator;
use SourceBroker\T3api\OpenApi\Exceptions\ValidationException;
use SourceBroker\T3api\OpenApi\Objects\BaseObject;
use SourceBroker\T3api\OpenApi\Objects\Components;
use SourceBroker\T3api\OpenApi\Objects\ExternalDocs;
use SourceBroker\T3api\OpenApi\Objects\Info;
use SourceBroker\T3api\OpenApi\Objects\PathItem;
use SourceBroker\T3api\OpenApi\Objects\SecurityRequirement;
use SourceBroker\T3api\OpenApi\Objects\Server;
use SourceBroker\T3api\OpenApi\Objects\Tag;
use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property string|null $openapi
 * @property \SourceBroker\T3api\OpenApi\Objects\Info|null $info
 * @property \SourceBroker\T3api\OpenApi\Objects\Server[]|null $servers
 * @property \SourceBroker\T3api\OpenApi\Objects\PathItem[]|null $paths
 * @property \SourceBroker\T3api\OpenApi\Objects\Components|null $components
 * @property \SourceBroker\T3api\OpenApi\Objects\SecurityRequirement[]|null $security
 * @property \SourceBroker\T3api\OpenApi\Objects\Tag[]|null $tags
 * @property \SourceBroker\T3api\OpenApi\Objects\ExternalDocs|null $externalDocs
 */
class OpenApi extends BaseObject
{
    public const OPENAPI_3_0_0 = '3.0.0';
    public const OPENAPI_3_0_1 = '3.0.1';
    public const OPENAPI_3_0_2 = '3.0.2';

    /**
     * @var string|null
     */
    protected $openapi;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Info|null
     */
    protected $info;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Server[]|null
     */
    protected $servers;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\PathItem[]|null
     */
    protected $paths;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Components|null
     */
    protected $components;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\SecurityRequirement[]|null
     */
    protected $security;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Tag[]|null
     */
    protected $tags;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\ExternalDocs|null
     */
    protected $externalDocs;

    /**
     * @param string|null $openapi
     * @return static
     */
    public function openapi(?string $openapi): self
    {
        $instance = clone $this;

        $instance->openapi = $openapi;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Info|null $info
     * @return static
     */
    public function info(?Info $info): self
    {
        $instance = clone $this;

        $instance->info = $info;

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
     * @param \SourceBroker\T3api\OpenApi\Objects\PathItem ...$paths
     * @return static
     */
    public function paths(PathItem ...$paths): self
    {
        $instance = clone $this;

        $instance->paths = $paths ?: null;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Components|null $components
     * @return static
     */
    public function components(?Components $components): self
    {
        $instance = clone $this;

        $instance->components = $components;

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

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Tag ...$tags
     * @return static
     */
    public function tags(Tag ...$tags): self
    {
        $instance = clone $this;

        $instance->tags = $tags ?: null;

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
     * @throws \SourceBroker\T3api\OpenApi\Exceptions\ValidationException
     */
    public function validate(): void
    {
        if (!class_exists('JsonSchema\Validator')) {
            throw new \RuntimeException('justinrainbow/json-schema should be installed for validation');
        }

        $data = BaseConstraint::arrayToObjectRecursive($this->generate());

        $schema = file_get_contents(
            realpath(__DIR__ . '/schemas/v3.0.json')
        );
        $schema = json_decode($schema);

        $validator = new Validator();
        $validator->validate($data, $schema);

        if (!$validator->isValid()) {
            throw new ValidationException($validator->getErrors());
        }
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        $paths = [];
        foreach ($this->paths ?? [] as $path) {
            $paths[$path->route ?? ''] = $path;
        }

        return Arr::filter([
            'openapi' => $this->openapi,
            'info' => $this->info,
            'servers' => $this->servers,
            'paths' => $paths ?: null,
            'components' => $this->components,
            'security' => $this->security,
            'tags' => $this->tags,
            'externalDocs' => $this->externalDocs,
        ]);
    }
}
