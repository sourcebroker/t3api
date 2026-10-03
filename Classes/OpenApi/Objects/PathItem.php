<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property string|null $route
 * @property string|null $summary
 * @property string|null $description
 * @property \SourceBroker\T3api\OpenApi\Objects\Operation[]|null $operations
 * @property \SourceBroker\T3api\OpenApi\Objects\Server[]|null $servers
 * @property \SourceBroker\T3api\OpenApi\Objects\Parameter[]|null $parameters
 */
class PathItem extends BaseObject
{
    /**
     * @var string|null
     */
    protected $route;

    /**
     * @var string|null
     */
    protected $summary;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Operation[]|null
     */
    protected $operations;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Server[]|null
     */
    protected $servers;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Parameter[]|null
     */
    protected $parameters;

    /**
     * @param string|null $route
     * @return static
     */
    public function route(?string $route): self
    {
        $instance = clone $this;

        $instance->route = $route;

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
     * @param \SourceBroker\T3api\OpenApi\Objects\Operation ...$operations
     * @return static
     */
    public function operations(Operation ...$operations): self
    {
        $instance = clone $this;

        $instance->operations = $operations ?: null;

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
     * @return array
     */
    protected function generate(): array
    {
        $operations = [];
        foreach ($this->operations ?? [] as $operation) {
            $operations[$operation->action ?? ''] = $operation->toArray();
        }

        return Arr::filter(
            array_merge($operations, [
                'summary' => $this->summary,
                'description' => $this->description,
                'servers' => $this->servers,
                'parameters' => $this->parameters,
            ])
        );
    }
}
