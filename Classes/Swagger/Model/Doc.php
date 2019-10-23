<?php

namespace SourceBroker\T3api\Swagger\Model;

class Doc
    extends AbstractModel
{

    /**
     * @var string
     */
    protected $swagger;

    /**
     * @var Info
     */
    protected $info;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var Tag[]|null
     */
    protected $tags;

    /**
     * @var string[]
     */
    protected $schemes;

    /**
     * @var Path[]
     */
    protected $paths;

    /**
     * @var SecurityDefinition[]|null
     */
    protected $securityDefinition;

    /**
     * @var Definition[]
     */
    protected $definitions;

    /**
     * @var array|null
     */
    protected $externalDoc;


    public function getSwagger(): string
    {
        return $this->swagger;
    }

    public function setSwagger(string $swagger): Doc
    {
        $this->swagger = $swagger;
        return $this;
    }

    public function getInfo(): Info
    {
        return $this->info;
    }

    public function setInfo(Info $info): Doc
    {
        $this->info = $info;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): Doc
    {
        $this->host = $host;
        return $this;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): Doc
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): Doc
    {
        $this->tags = $tags;
        return $this;
    }

    public function getSchemes(): array
    {
        return $this->schemes;
    }

    public function setSchemes(array $schemes): Doc
    {
        $this->schemes = $schemes;
        return $this;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function setPaths(array $paths): Doc
    {
        $this->paths = $paths;
        return $this;
    }

    public function getSecurityDefinition(): ?array
    {
        return $this->securityDefinition;
    }

    public function setSecurityDefinition(?array $securityDefinition): Doc
    {
        $this->securityDefinition = $securityDefinition;
        return $this;
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function setDefinitions(array $definitions): Doc
    {
        $this->definitions = $definitions;
        return $this;
    }

    public function getExternalDoc(): ?array
    {
        return $this->externalDoc;
    }

    public function setExternalDoc(?array $externalDoc): Doc
    {
        $this->externalDoc = $externalDoc;
        return $this;
    }

}
