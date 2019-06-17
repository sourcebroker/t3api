<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Domain\Model;

use SourceBroker\Restify\Annotation\ApiFilter as ApiFilterAnnotation;

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
     * ApiFilter constructor.
     *
     * @param string $filterClass
     * @param string $property
     * @param string $strategy
     */
    public function __construct(string $filterClass, string $property, string $strategy)
    {
        $this->filterClass = $filterClass;
        $this->property = $property;
        $this->strategy = $strategy;
    }

    /**
     * @param ApiFilterAnnotation $apiFilterAnnotation
     *
     * @return self[]
     */
    public static function createFromAnnotations(ApiFilterAnnotation $apiFilterAnnotation): array
    {
        $instances = [];

        foreach ($apiFilterAnnotation->getProperties() as $property => $strategy) {
            $instances[] = new static($apiFilterAnnotation->getFilterClass(), $property, $strategy);
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
}
