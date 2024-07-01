<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Event;

use SourceBroker\T3api\Domain\Model\OperationInterface;

final class AfterProcessOperationEvent
{
    private OperationInterface $operation;

    /**
     * @var mixed
     */
    private $result;

    public function __construct(OperationInterface $operation, $result)
    {
        $this->operation = $operation;
        $this->result = $result;
    }

    public function getOperation(): OperationInterface
    {
        return $this->operation;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result): void
    {
        $this->result = $result;
    }
}
