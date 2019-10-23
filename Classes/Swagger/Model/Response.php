<?php

namespace SourceBroker\T3api\Swagger\Model;

class Response
    extends AbstractKeyModel
{

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var array|null
     */
    protected $schema;


    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Response
    {
        $this->description = $description;
        return $this;
    }

    public function getSchema(): ?array
    {
        return $this->schema;
    }

    public function setSchema(?array $schema): Response
    {
        $this->schema = $schema;
        return $this;
    }

}
