<?php

namespace SourceBroker\T3api\Swagger\Model;

class SecurityDefinition
    extends AbstractKeyModel
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $in;

    /**
     * @var string|null
     */
    protected $authorizationUrl;

    /**
     * @var string|null
     */
    protected $flow;

    /**
     * @var array|null
     */
    protected $scopes;


    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): SecurityDefinition
    {
        $this->type = $type;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): SecurityDefinition
    {
        $this->name = $name;
        return $this;
    }

    public function getIn(): ?string
    {
        return $this->in;
    }

    public function setIn(?string $in): SecurityDefinition
    {
        $this->in = $in;
        return $this;
    }

    public function getAuthorizationUrl(): ?string
    {
        return $this->authorizationUrl;
    }

    public function setAuthorizationUrl(?string $authorizationUrl): SecurityDefinition
    {
        $this->authorizationUrl = $authorizationUrl;
        return $this;
    }

    public function getFlow(): ?string
    {
        return $this->flow;
    }

    public function setFlow(?string $flow): SecurityDefinition
    {
        $this->flow = $flow;
        return $this;
    }

    public function getScopes(): ?array
    {
        return $this->scopes;
    }

    public function setScopes(?array $scopes): SecurityDefinition
    {
        $this->scopes = $scopes;
        return $this;
    }

}
