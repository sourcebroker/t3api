<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Event\BeforeOperationAccessGrantedEvent;
use SourceBroker\T3api\Event\BeforeOperationAccessGrantedPostDenormalizeEvent;

class OperationAccessChecker extends AbstractAccessChecker
{
    public function isGranted(OperationInterface $operation, array $expressionLanguageVariables = []): bool
    {
        $event = new BeforeOperationAccessGrantedEvent(
            $operation,
            $expressionLanguageVariables
        );
        $this->eventDispatcher->dispatch($event);
        $expressionLanguageVariables = $event->getExpressionLanguageVariables();

        if (!$operation->getSecurity()) {
            return true;
        }

        $variables = array_merge($expressionLanguageVariables, ['t3apiOperation' => $operation]);

        return $this->getExpressionLanguageResolver($variables)->evaluate($operation->getSecurity());
    }

    public function isGrantedPostDenormalize(
        OperationInterface $operation,
        array $expressionLanguageVariables = []
    ): bool {
        $event = new BeforeOperationAccessGrantedPostDenormalizeEvent(
            $operation,
            $expressionLanguageVariables
        );
        $this->eventDispatcher->dispatch($event);
        $expressionLanguageVariables = $event->getExpressionLanguageVariables();

        if (!$operation->getSecurityPostDenormalize()) {
            return true;
        }

        $variables = array_merge($expressionLanguageVariables, ['t3apiOperation' => $operation]);

        return $this->getExpressionLanguageResolver($variables)->evaluate($operation->getSecurityPostDenormalize());
    }
}
