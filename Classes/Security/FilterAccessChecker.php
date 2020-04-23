<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class OperationAccessChecker
 */
class FilterAccessChecker extends AbstractAccessChecker
{

    /**
     * @param ApiFilter $filter
     * @param array $expressionLanguageVariables
     *
     * @return bool
     */
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
