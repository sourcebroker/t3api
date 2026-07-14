<?php

declare(strict_types=1);

namespace SourceBroker\T3api\ExpressionLanguage;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Registers t3api's own expression functions and variables for the `t3api` expression-language
 * context, shared by `security`/`security_post_denormalize` and the response cache
 * `readCondition`/`writeCondition`/`identifierExpressions` expressions.
 */
class T3apiCoreProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            T3apiCoreFunctionsProvider::class,
        ];

        // Exposes the TYPO3 `Context` object as `context`, mirroring the variable TYPO3 core
        // itself passes into TypoScript conditions
        // (`IncludeTreeConditionMatcherVisitor::initializeExpressionMatcherWithVariables()`), so
        // an expression can read any Context aspect directly, e.g.
        // `context.getPropertyFromAspect('frontend.user', 'groupIds', '')`.
        $this->expressionLanguageVariables = [
            'context' => GeneralUtility::makeInstance(Context::class),
        ];
    }
}
