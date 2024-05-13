<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Annotation\ApiFilter as ApiFilterAnnotation;
use SourceBroker\T3api\Filter\FilterInterface;
use SourceBroker\T3api\Filter\OrderFilter;

class ApiFilter
{
    protected string $filterClass;

    protected ApiFilterStrategy $strategy;

    protected string $property;

    protected array $arguments = [];

    public function __construct(string $filterClass, string $property, array|string $strategy, array $arguments)
    {
        $this->filterClass = $filterClass;
        $this->property = $property;
        $this->strategy = new ApiFilterStrategy($strategy);
        $this->arguments = $arguments;
    }

    /**
     * @return self[]
     */
    public static function createFromAnnotations(ApiFilterAnnotation $apiFilterAnnotation): array
    {
        /** @var string|FilterInterface $filterClass */
        $filterClass = $apiFilterAnnotation->getFilterClass();

        $arguments = $apiFilterAnnotation->getArguments();
        if (property_exists($filterClass, 'defaultArguments')) {
            if (!is_array($filterClass::$defaultArguments)) {
                throw new \InvalidArgumentException(
                    sprintf('%s::$defaultArguments has to be an array', $filterClass),
                    1582290496996
                );
            }
            $arguments = array_merge($filterClass::$defaultArguments, $arguments);
        }

        // In case when properties are not determined we still want to register filter.
        // Needed e.g. in `\SourceBroker\T3api\Filter\DistanceFilter` which is not based on single property
        //    and properties are determined inside of arguments.
        if ($apiFilterAnnotation->getProperties() === []) {
            return [new static($apiFilterAnnotation->getFilterClass(), '', '', $arguments)];
        }

        $instances = [];
        foreach ($apiFilterAnnotation->getProperties() as $property => $strategy) {
            $instances[] = new static(
                $apiFilterAnnotation->getFilterClass(),
                $property,
                $strategy,
                $arguments
            );
        }

        return $instances;
    }

    public function getFilterClass(): string
    {
        return $this->filterClass;
    }

    public function getStrategy(): ApiFilterStrategy
    {
        return $this->strategy;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return mixed
     */
    public function getArgument(string $argumentName)
    {
        return $this->getArguments()[$argumentName] ?? null;
    }

    public function getParameterName(): string
    {
        if ($this->isOrderFilter()) {
            $plainParameterName = $this->getArgument('orderParameterName');
        } else {
            $plainParameterName = $this->getArgument('parameterName') ?? $this->getProperty();
        }

        // PHP automatically replaces some characters in variable names, which also affects GET parameters
        // https://www.php.net/variables.external#language.variables.external.dot-in-names
        return str_replace('.', '_', $plainParameterName);
    }

    public function isOrderFilter(): bool
    {
        return is_a($this->filterClass, OrderFilter::class, true);
    }
}
