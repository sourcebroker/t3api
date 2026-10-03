<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Exceptions\InvalidArgumentException;
use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property string|null $propertyName
 * @property array|null $mapping
 */
class Discriminator extends BaseObject
{
    /**
     * @var string|null
     */
    protected $propertyName;

    /**
     * @var array|null
     */
    protected $mapping;

    /**
     * @param string|null $propertyName
     * @return static
     */
    public function propertyName(?string $propertyName): self
    {
        $instance = clone $this;

        $instance->propertyName = $propertyName;

        return $instance;
    }

    /**
     * @param array $mapping
     * @throws \SourceBroker\T3api\OpenApi\Exceptions\InvalidArgumentException
     * @return static
     */
    public function mapping(array $mapping): self
    {
        // Ensure the mappings are string => string.
        foreach ($mapping as $key => $value) {
            if (is_string($key) && is_string($value)) {
                continue;
            }

            throw new InvalidArgumentException('Each mapping must have a string key and a string value.');
        }

        $instance = clone $this;

        $instance->mapping = $mapping ?: null;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        return Arr::filter([
            'propertyName' => $this->propertyName,
            'mapping' => $this->mapping,
        ]);
    }
}
