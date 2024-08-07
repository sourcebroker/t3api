<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\ContextBuilder;

use JMS\Serializer\Context;
use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractContextBuilder implements ContextBuilderInterface
{
    public function __construct(protected readonly EventDispatcherInterface $eventDispatcher) {}

    protected function dispatchAfterCreateContextForOperationEvent(
        OperationInterface $operation,
        Request $request,
        Context $context
    ): void {
        $this->eventDispatcher->dispatch(
            new AfterCreateContextForOperationEvent(
                $operation,
                $request,
                $context
            )
        );
    }
}
