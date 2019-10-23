<?php

namespace SourceBroker\T3api\Swagger\Model;

class Parameter
    extends AbstractModel
{

    /**
     * @var string
     */
    protected $in;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var array|null
     */
    protected $schema;


    public function getIn(): string
    {
        return $this->in;
    }

    public function setIn(string $in): Parameter
    {
        $this->in = $in;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Parameter
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Parameter
    {
        $this->description = $description;
        return $this;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): Parameter
    {
        $this->required = $required;
        return $this;
    }

    public function getSchema(): ?array
    {
        return $this->schema;
    }

    public function setSchema(?array $schema): Parameter
    {
        $this->schema = $schema;
        return $this;
    }

}
