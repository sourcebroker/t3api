<?php

namespace SourceBroker\T3api\Swagger\Model;

class Definition
    extends AbstractKeyModel
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Property[]
     */
    protected $properties = [];

    /**
     * @var string[]|null
     */
    protected $required = [];

    /**
     * @var array|null
     */
    protected $xml;


    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Definition
    {
        $this->type = $type;
        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): Definition
    {
        $this->properties = $properties;
        return $this;
    }

    public function getRequired(): ?array
    {
        return $this->required;
    }

    public function setRequired(?array $required): Definition
    {
        $this->required = $required;
        return $this;
    }

    public function getXml(): ?array
    {
        return $this->xml;
    }

    public function setXml(?array $xml): Definition
    {
        $this->xml = $xml;
        return $this;
    }

}
