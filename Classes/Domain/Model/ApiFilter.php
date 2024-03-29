<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Annotation\ApiFilter as ApiFilterAnnotation;
use SourceBroker\T3api\Filter\FilterInterface;
use SourceBroker\T3api\Filter\OrderFilter;

/**
 * Class ApiFilter
 */
class ApiFilter
{
    /**
     * @var string
     */
    protected $filterClass;

    /**
     * @var ApiFilterStrategy
     */
    protected $strategy;

    /**
     * @var string
     */
    protected $property;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * ApiFilter constructor.
     *
     * @param string $filterClass
     * @param string $property
     * @param string|array $strategy
     * @param array $arguments
     */
    public function __construct(string $filterClass, string $property, $strategy, array $arguments)
    {
        $this->filterClass = $filterClass;
        $this->property = $property;
        $this->strategy = new ApiFilterStrategy($strategy);
        $this->arguments = $arguments;
    }

    /**
     * @param ApiFilterAnnotation $apiFilterAnnotation
     *
     * @return self[]
     */
    public static function createFromAnnotations(ApiFilterAnnotation $apiFilterAnnotation): array
    {
        /** @var string|FilterInterface $filterClass */
        $filterClass = $apiFilterAnnotation->getFilterClass();

        $arguments = $apiFilterAnnotation->getArguments();
        if (property_exists($filterClass, 'defaultArguments')) {
            if (!is_array($filterClass::$defaultArguments)) {
                throw new \InvalidArgumentException(sprintf('%s::$defaultArguments has to be an array', $filterClass), 1582290496996);
            }
            $arguments = array_merge($filterClass::$defaultArguments, $arguments);
        }

        // In case when properties are not determined we still want to register filter.
        // Needed e.g. in `\SourceBroker\T3api\Filter\DistanceFilter` which is not based on single property
        //    and properties are determined inside of arguments.
        if (empty($apiFilterAnnotation->getProperties())) {
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

    /**
     * @return string
     */
    public function getFilterClass(): string
    {
        return $this->filterClass;
    }

    public function getStrategy(): ApiFilterStrategy
    {
        return $this->strategy;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param string $argumentName
     *
     * @return mixed
     */
    public function getArgument(string $argumentName)
    {
        return $this->getArguments()[$argumentName] ?? null;
    }

    /**
     * @return string
     */
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

    /**
     * @return bool
     */
    public function isOrderFilter(): bool
    {
        return is_a($this->filterClass, OrderFilter::class, true);
    }
}
