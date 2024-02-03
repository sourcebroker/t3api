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

        $resolver = $this->getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurity());
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

        $resolver = $this->getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurityPostDenormalize());
    }
}
