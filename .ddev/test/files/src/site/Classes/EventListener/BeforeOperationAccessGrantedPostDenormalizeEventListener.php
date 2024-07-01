<?php

namespace V\Site\EventListener;

use SourceBroker\T3api\Event\BeforeOperationAccessGrantedPostDenormalizeEvent;
use V\Site\Logger\HtmlFileLogger;

class BeforeOperationAccessGrantedPostDenormalizeEventListener
{
    public function __construct(
        private readonly HtmlFileLogger $logger,
    ) {}

    public function __invoke(BeforeOperationAccessGrantedPostDenormalizeEvent $event): void
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
            'expressionLanguageVariables' => $event->getExpressionLanguageVariables(),
        ];

        $this->logger->warning($logMessage, $logContext);
    }
}

