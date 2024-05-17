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

        $variables = array_merge($expressionLanguageVariables, ['t3apiFilter' => $filter]);

        return $this->getExpressionLanguageResolver($variables)->evaluate($filter->getStrategy()->getCondition());
    }
}
