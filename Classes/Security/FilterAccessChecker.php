<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\ApiFilter;

class FilterAccessChecker extends AbstractAccessChecker
{
    public static function isGranted(ApiFilter $filter, array $expressionLanguageVariables = []): bool
    {
        if (empty($filter->getStrategy()->getCondition())) {
            return true;
        }

        $resolver = self::getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiFilter'] = $filter;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($filter->getStrategy()->getCondition());
    }
}
