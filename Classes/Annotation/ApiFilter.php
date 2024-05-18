<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation;

use SourceBroker\T3api\Filter\FilterInterface;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ApiFilter
{
    protected string $strategy = '';

    protected string $filterClass;

    protected array $properties = [];

    protected array $arguments = [];

    public function __construct(array $options = [])
    {
        if (!is_string($options['value'] ?? null)) {
            throw new \InvalidArgumentException(
                sprintf('`%s` Annotation needs a value representing the filter class.', self::class),
                1581881033567
            );
        }

        $filterClass = $options['value'];

        if (!is_a($filterClass, FilterInterface::class, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The filter class `%s` does not extends `%s`.%s',
                    $options['value'],
                    FilterInterface::class,
                    substr_count($options['value'], '\\') < 2 ? ' Did you forget to use `use` statement?' : ''
                ),
                1581882087932
            );
        }

        $this->filterClass = $filterClass;
        $this->properties = $options['properties'] ?? $this->properties;
        $this->strategy = $options['strategy'] ?? $this->strategy;
        $this->arguments = $options['arguments'] ?? $this->arguments;
    }

    public function getProperties(): array
    {
        $properties = [];

        foreach ($this->properties as $propertyName => $strategy) {
            if (is_numeric($propertyName)) {
                $propertyName = $strategy;
                $strategy = $this->strategy;
            }

            $properties[$propertyName] = $strategy;
        }

        return $properties;
    }

    public function getFilterClass(): string
    {
        return $this->filterClass;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
