<?php

namespace SourceBroker\T3api\Annotation;

use SourceBroker\T3api\Filter\AbstractFilter;
use InvalidArgumentException;

/**
 * ApiResource annotation.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class ApiFilter
{
    /**
     * @var string
     */
    protected $strategy = '';

    /**
     * @var string
     */

    protected $filterClass;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * ApiFilter constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (!is_string($options['value'] ?? null)) {
            throw new InvalidArgumentException('This annotation needs a value representing the filter class.');
        }

        $filterClass = $options['value'];

        if (!is_a($filterClass, AbstractFilter::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'The filter class "%s" does not extends "%s".',
                $options['value'],
                AbstractFilter::class
            ));
        }

        $this->filterClass = $filterClass;
        $this->properties = $options['properties'] ?? $this->properties;
        $this->strategy = $options['strategy'] ?? $this->strategy;
        $this->arguments = $options['arguments'] ?? $this->arguments;
    }

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    public function getFilterClass(): string
    {
        return $this->filterClass;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
