<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Event\BeforeFilterAccessGrantedEvent;

class FilterAccessChecker extends AbstractAccessChecker
{
    public function isGranted(ApiFilter $filter, array $expressionLanguageVariables = []): bool
    {
        $event = new BeforeFilterAccessGrantedEvent(
            $filter,
            $expressionLanguageVariables
        );
        $this->eventDispatcher->dispatch($event);
        $expressionLanguageVariables = $event->getExpressionLanguageVariables();

        if (empty($filter->getStrategy()->getCondition())) {
            return true;
        }

        $resolver = $this->getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiFilter'] = $filter;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($filter->getStrategy()->getCondition());
    }
}
