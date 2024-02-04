<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Event;

use JMS\Serializer\Context;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;

class AfterCreateContextForOperationEvent
{
    /**
     * @var OperationInterface
     */
    protected $operation;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        OperationInterface $operation,
        Request $request,
        Context $context
    ) {
        $this->operation = $operation;
        $this->request = $request;
        $this->context = $context;
    }

    public function getOperation(): OperationInterface
    {
        return $this->operation;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
