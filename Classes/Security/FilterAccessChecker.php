<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class FilterAccessChecker extends AbstractAccessChecker
{
    public const SIGNAL_BEFORE_IS_GRANTED = 'beforeIsGranted';

    public function isGranted(ApiFilter $filter, array $expressionLanguageVariables = []): bool
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        [$filter, $expressionLanguageVariables] = $this->objectManager->get(Dispatcher::class)
            ->dispatch(self::class, self::SIGNAL_BEFORE_IS_GRANTED, [$filter, $expressionLanguageVariables]);

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
