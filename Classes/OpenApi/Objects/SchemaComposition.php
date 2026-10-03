<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Contracts\SchemaContract;
use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property \SourceBroker\T3api\OpenApi\Objects\Schema[]|null $schemas
 */
abstract class SchemaComposition extends BaseObject implements SchemaContract
{
    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Schema[]|null
     */
    protected $schemas;

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Schema ...$schemas
     * @return static
     */
    public function schemas(Schema ...$schemas): self
    {
        $instance = clone $this;

        $instance->schemas = $schemas ?: null;

        return $instance;
    }

    /**
     * @return string
     */
    abstract protected function compositionType(): string;

    /**
     * @return array
     */
    protected function generate(): array
    {
        return Arr::filter([
            $this->compositionType() => $this->schemas,
        ]);
    }
}
