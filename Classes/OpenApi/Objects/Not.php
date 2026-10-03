<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Contracts\SchemaContract;
use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property \SourceBroker\T3api\OpenApi\Objects\Schema|null $schema
 */
class Not extends BaseObject implements SchemaContract
{
    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Schema|null
     */
    protected $schema;

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Schema|null $schema
     * @return static
     */
    public function schema(?Schema $schema): self
    {
        $instance = clone $this;

        $instance->schema = $schema;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        return Arr::filter([
            'not' => $this->schema,
        ]);
    }
}
