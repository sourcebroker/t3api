<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\ApiFilter;

class FilterAccessChecker extends AbstractAccessChecker
{
    public function isGranted(ApiFilter $filter, array $expressionLanguageVariables = []): bool
    {
        if (empty($filter->getStrategy()->getCondition())) {
            return true;
        }

        if ($this->shouldUseLegacyCheckMethod()) {
            return $this->isGrantedLegacy($filter, $expressionLanguageVariables);
        }

        $resolver = $this->getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiFilter'] = $filter;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($filter->getStrategy()->getCondition());
    }

    /**
     * @deprecated
     * @noinspection PhpDocSignatureInspection
     * @todo Remove when support for version lower than 9.4 is dropped
     */
    public function isGrantedLegacy(ApiFilter $filter, array $expressionLanguageVariables = []): bool
    {
        return (bool)$this->evaluateLegacyExpressionLanguage(
            $filter->getStrategy()->getCondition(),
            array_merge(['t3apiFilter' => $filter], $expressionLanguageVariables)
        );
    }
}
