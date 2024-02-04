<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Event;

use SourceBroker\T3api\Domain\Model\OperationInterface;

class BeforeOperationAccessGrantedEvent
{
    /**
     * @var OperationInterface
     */
    private $operation;

    /**
     * @var array
     */
    private $expressionLanguageVariables;

    public function __construct(
        OperationInterface $operation,
        array $expressionLanguageVariables = []
    ) {
        $this->operation = $operation;
        $this->expressionLanguageVariables = $expressionLanguageVariables;
    }

    public function getOperation(): OperationInterface
    {
        return $this->operation;
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
