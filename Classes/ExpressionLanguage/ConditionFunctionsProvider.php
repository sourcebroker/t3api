<?php

declare(strict_types=1);
namespace SourceBroker\T3api\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use TYPO3\CMS\Core\ExpressionLanguage\FunctionsProvider\Typo3ConditionFunctionsProvider;

/**
 * Class ConditionFunctionsProvider
 */
class ConditionFunctionsProvider extends Typo3ConditionFunctionsProvider
{
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions()
    {
        return parent::getFunctions();
    }
}
