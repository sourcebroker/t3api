<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Model;

use SourceBroker\Restify\Annotation\ApiFilter as ApiFilterAnnotation;
use SourceBroker\Restify\Filter\AbstractFilter;
use SourceBroker\Restify\Filter\OrderFilter;

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
     * @var string
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
     * @param string $strategy
     * @param array $arguments
     */
    public function __construct(string $filterClass, string $property, string $strategy, array $arguments)
    {
        $this->filterClass = $filterClass;
        $this->property = $property;
        $this->strategy = $strategy;
        $this->arguments = $arguments;
    }

    /**
     * @param ApiFilterAnnotation $apiFilterAnnotation
     *
     * @return self[]
     */
    public static function createFromAnnotations(ApiFilterAnnotation $apiFilterAnnotation): array
    {
        /** @var string|AbstractFilter $filterClass */
        $filterClass = $apiFilterAnnotation->getFilterClass();
        $instances = [];

        foreach ($apiFilterAnnotation->getProperties() as $property => $strategy) {
            $instances[] = new static(
                $apiFilterAnnotation->getFilterClass(),
                $property,
                $strategy,
                array_merge($filterClass::$defaultArguments, $apiFilterAnnotation->getArguments())
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

    /**
     * @return string
     */
    public function getStrategy(): string
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
        switch ($this->filterClass) {
            case OrderFilter::class:
                return $this->getArgument('orderParameterName');
            default:
                return $this->getProperty();
        }
    }
}
