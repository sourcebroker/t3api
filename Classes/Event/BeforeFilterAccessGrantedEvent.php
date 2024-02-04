<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Event;

use SourceBroker\T3api\Domain\Model\ApiFilter;

class BeforeFilterAccessGrantedEvent
{
    /**
     * @var ApiFilter
     */
    private $filter;

    /**
     * @var array
     */
    private $expressionLanguageVariables;

    public function __construct(
        ApiFilter $filter,
        array $expressionLanguageVariables = []
    ) {
        $this->filter = $filter;
        $this->expressionLanguageVariables = $expressionLanguageVariables;
    }

    public function getFilter(): ApiFilter
    {
        return $this->filter;
    }

    public function getExpressionLanguageVariables(): array
    {
        return $this->expressionLanguageVariables;
    }

    public function setExpressionLanguageVariable(string $name, $value): void
    {
        $this->expressionLanguageVariables[$name] = $value;
    }
}
