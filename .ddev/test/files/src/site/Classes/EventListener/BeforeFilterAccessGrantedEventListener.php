<?php

namespace V\Site\EventListener;

use SourceBroker\T3api\Event\BeforeFilterAccessGrantedEvent;
use V\Site\Logger\HtmlFileLogger;

class BeforeFilterAccessGrantedEventListener
{
    public function __construct(
        private readonly HtmlFileLogger $logger,
    ) {}

    public function __invoke(BeforeFilterAccessGrantedEvent $event): void
    {
        $filter = $event->getFilter();

        $logMessage = sprintf(
            '<strong>Filter processed:</strong> %s<br>',
            $filter->getFilterClass(),
        );

        $logContext = [
            'filter' => $filter,
            'expressionLanguageVariables' => $event->getExpressionLanguageVariables(),
        ];

        $this->logger->warning($logMessage, $logContext);
    }
}

