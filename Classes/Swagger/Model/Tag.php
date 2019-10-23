<?php

namespace SourceBroker\T3api\Swagger\Model;

class Tag
    extends AbstractModel
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var array|null
     */
    protected $externalDocs;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Tag
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Tag
    {
        $this->description = $description;
        return $this;
    }

    public function getExternalDocs(): ?array
    {
        return $this->externalDocs;
    }

    public function setExternalDocs(?array $externalDocs): Tag
    {
        $this->externalDocs = $externalDocs;
        return $this;
    }

}
