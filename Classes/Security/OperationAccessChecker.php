<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\AbstractOperation;

class OperationAccessChecker extends AbstractAccessChecker
{
    public static function isGranted(AbstractOperation $operation, array $expressionLanguageVariables = []): bool
    {
        if (!$operation->getSecurity()) {
            return true;
        }

        if (static::shouldUseLegacyCheckMethod()) {
            return static::isGrantedLegacy($operation, $expressionLanguageVariables);
        }

        $resolver = static::getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurity());
    }

    /**
     * @deprecated
     * @todo Remove when support for version lower than 9.4 is dropped
     */
    public static function isGrantedLegacy(AbstractOperation $operation, array $expressionLanguageVariables = []): bool
    {
        return (bool)static::evaluateLegacyExpressionLanguage(
            $operation->getSecurity(),
            array_merge(['t3apiOperation' => $operation], $expressionLanguageVariables)
        );
    }

    public static function isGrantedPostDenormalize(AbstractOperation $operation, array $expressionLanguageVariables = []): bool
    {
        if (!$operation->getSecurityPostDenormalize()) {
            return true;
        }

        if (static::shouldUseLegacyCheckMethod()) {
            return static::isGrantedPostDenormalizeLegacy($operation, $expressionLanguageVariables);
        }

        $resolver = static::getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurityPostDenormalize());
    }

    /**
     * @deprecated
     * @todo Remove when support for version lower than 9.4 is dropped
     */
    public static function isGrantedPostDenormalizeLegacy(AbstractOperation $operation, array $expressionLanguageVariables = []): bool
    {
        return (bool)static::evaluateLegacyExpressionLanguage(
            $operation->getSecurityPostDenormalize(),
            array_merge(['t3apiOperation' => $operation], $expressionLanguageVariables)
        );
    }
}
