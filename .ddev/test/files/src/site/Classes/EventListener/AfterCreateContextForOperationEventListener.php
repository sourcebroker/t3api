<?php

namespace V\Site\EventListener;

use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;
use V\Site\Logger\HtmlFileLogger;

class AfterCreateContextForOperationEventListener
{
    public function __construct(
        private readonly HtmlFileLogger $logger,
    ) {
    }

    public function __invoke(AfterCreateContextForOperationEvent $event): void
    {
        $operation = $event->getOperation();
        $logMessage = sprintf(
            '
            <strong>Operation processed:</strong> %s<br>
            <strong>Method:</strong> %s<br>
            <strong>Path:</strong> %s
            ',
            get_class($operation),
            $operation->getMethod(),
            $operation->getPath()
        );

        $logContext = [
            'operation' => $operation,
            'request' => $event->getRequest(),
            'context' => $event->getContext(),
        ];

        $this->logger->warning($logMessage, $logContext);
    }
}

