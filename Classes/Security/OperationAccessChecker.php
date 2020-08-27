<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\OperationInterface;

class OperationAccessChecker extends AbstractAccessChecker
{
    public function isGranted(OperationInterface $operation, array $expressionLanguageVariables = []): bool
    {
        if (!$operation->getSecurity()) {
            return true;
        }

        if ($this->shouldUseLegacyCheckMethod()) {
            return $this->isGrantedLegacy($operation, $expressionLanguageVariables);
        }

        $resolver = $this->getExpressionLanguageResolver();
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
    public function isGrantedLegacy(OperationInterface $operation, array $expressionLanguageVariables = []): bool
    {
        return (bool)$this->evaluateLegacyExpressionLanguage(
            $operation->getSecurity(),
            array_merge(['t3apiOperation' => $operation], $expressionLanguageVariables)
        );
    }

    public function isGrantedPostDenormalize(
        OperationInterface $operation,
        array $expressionLanguageVariables = []
    ): bool {
        if (!$operation->getSecurityPostDenormalize()) {
            return true;
        }

        if ($this->shouldUseLegacyCheckMethod()) {
            return $this->isGrantedPostDenormalizeLegacy($operation, $expressionLanguageVariables);
        }

        $resolver = $this->getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurityPostDenormalize());
    }

    /**
     * @deprecated
     * @noinspection PhpDocSignatureInspection
     * @todo Remove when support for version lower than 9.4 is dropped
     */
    public function isGrantedPostDenormalizeLegacy(
        OperationInterface $operation,
        array $expressionLanguageVariables = []
    ): bool {
        return (bool)$this->evaluateLegacyExpressionLanguage(
            $operation->getSecurityPostDenormalize(),
            array_merge(['t3apiOperation' => $operation], $expressionLanguageVariables)
        );
    }
}
