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

        if (self::shouldUseLegacyCheckMethod()) {
            return static::isGrantedLegacy($filter, $expressionLanguageVariables);
        }

        $resolver = self::getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiFilter'] = $filter;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($filter->getStrategy()->getCondition());
    }

    /**
     * @deprecated
     * @todo Remove when support for version lower than 9.4 is dropped
     */
    public static function isGrantedLegacy(ApiFilter $filter, array $expressionLanguageVariables = []): bool
    {
        return (bool)static::evaluateLegacyExpressionLanguage(
            $filter->getStrategy()->getCondition(),
            array_merge(['t3apiFilter' => $filter], $expressionLanguageVariables)
        );
    }
}
