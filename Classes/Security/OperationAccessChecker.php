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

        $resolver = self::getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurity());
    }

    public static function isGrantedPostDenormalize(AbstractOperation $operation, array $expressionLanguageVariables = []): bool
    {
        if (!$operation->getSecurityPostDenormalize()) {
            return true;
        }

        $resolver = self::getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurityPostDenormalize());
    }
}
