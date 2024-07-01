<?php

namespace V\Site\EventListener;

use SourceBroker\T3api\Event\AfterDeserializeOperationEvent;
use V\Site\Logger\HtmlFileLogger;

class AfterDeserializeOperationEventListener
{
    public function __construct(
        private readonly HtmlFileLogger $logger,
    ) {}

    public function __invoke(AfterDeserializeOperationEvent $event): void
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
            'object' => $event->getObject(),
        ];

        $this->logger->warning($logMessage, $logContext);
    }
}

