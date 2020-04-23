<?php


namespace SourceBroker\T3api\Domain\Model;

class ApiFilterStrategy
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $condition;

    /**
     * ApiFilterStrategy constructor.
     *
     * @param string|array $strategy
     */
    public function __construct($strategy)
    {
        if (is_string($strategy)) {
            $this->name = !empty($strategy) ? $strategy : '';
        } elseif (is_array($strategy)) {
            $this->name = $strategy['name'] ?? '';
            $this->condition = $strategy['condition'] ?? '';
        } else {
            throw new \InvalidArgumentException(sprintf('%s::$strategy has to be either string or array', self::class), 1587649745234);
        }
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $condition
     */
    public function setCondition($condition): void
    {
        $this->condition = $condition;
    }

    /**
     * @return string $condition
     */
    public function getCondition(): ?string
    {
        return $this->condition;
    }
}
