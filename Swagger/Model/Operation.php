<?php

namespace SourceBroker\T3api\Swagger\Model;

class Operation
    extends AbstractKeyModel
{

    /**
     * @var string
     */
    protected $operationId;

    /**
     * @var string
     */
    protected $summary = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string[]
     */
    protected $tags;

    /**
     * @var string[]|null
     */
    protected $consumes;

    /**
     * @var string|null
     */
    protected $produces;

    /**
     * @var Parameter[]
     */
    protected $parameters = [];

    /**
     * @var Response[]
     */
    protected $responses;

    /**
     * @var array|null
     */
    protected $security;


    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function setOperationId(string $operationId): Operation
    {
        $this->operationId = $operationId;
        return $this;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): Operation
    {
        $this->summary = $summary;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Operation
    {
        $this->description = $description;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): Operation
    {
        $this->tags = $tags;
        return $this;
    }

    public function getConsumes(): ?array
    {
        return $this->consumes;
    }

    public function setConsumes(?array $consumes): Operation
    {
        $this->consumes = $consumes;
        return $this;
    }

    public function getProduces(): ?string
    {
        return $this->produces;
    }

    public function setProduces(?string $produces): Operation
    {
        $this->produces = $produces;
        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): Operation
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function setResponses(array $responses): Operation
    {
        $this->responses = $responses;
        return $this;
    }

    public function getSecurity(): ?array
    {
        return $this->security;
    }

    public function setSecurity(?array $security): Operation
    {
        $this->security = $security;
        return $this;
    }

}
