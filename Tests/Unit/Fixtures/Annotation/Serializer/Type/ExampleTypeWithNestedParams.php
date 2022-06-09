<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Annotation\Serializer\Type;

use SourceBroker\T3api\Annotation\Serializer\Type\TypeInterface;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class ExampleTypeWithNestedParams implements TypeInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var array
     */
    protected $config = [];

    public function __construct($options = [])
    {
        $this->value = $options['value'];
        $this->config = $options['config'] ?? $this->config;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [
            $this->value,
            $this->config,
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'ExampleTypeWithNestedParams';
    }
}
