<?php

namespace SourceBroker\T3api\Swagger\Model;

class Property
    extends AbstractKeyModel
{

    /**
     * @var string
     */
    protected $_ref;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string|null
     */
    protected $format;

    /**
     * @var mixed|null
     */
    protected $default;

    /**
     * @var string[]|null
     */
    protected $enum;

    /**
     * @var array
     */
    protected $items;


    public function getRef(): string
    {
        return $this->_ref;
    }

    public function setRef(string $ref): Property
    {
        $this->_ref = $ref;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Property
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Property
    {
        $this->description = $description;
        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): Property
    {
        $this->format = $format;
        return $this;
    }

    public function getDefault(): ?mixed
    {
        return $this->default;
    }

    public function setDefault(?mixed $default): Property
    {
        $this->default = $default;
        return $this;
    }

    public function getEnum(): ?array
    {
        return $this->enum;
    }

    public function setEnum(?array $enum): Property
    {
        $this->enum = $enum;
        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): Property
    {
        $this->items = $items;
        return $this;
    }

}
