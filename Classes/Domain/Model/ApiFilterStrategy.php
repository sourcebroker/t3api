<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

class ApiFilterStrategy
{
    protected string $name;

    protected ?string $condition = null;

    public function __construct(string|array $strategy)
    {
        if (is_string($strategy)) {
            $this->name = !empty($strategy) ? $strategy : '';
        } elseif (is_array($strategy)) {
            $this->name = $strategy['name'] ?? '';
            $this->condition = $strategy['condition'] ?? '';
        } else {
            throw new \InvalidArgumentException(
                sprintf('%s::$strategy has to be either string or array', self::class),
                1587649745
            );
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }
}
