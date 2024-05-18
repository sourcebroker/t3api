<?php

declare(strict_types=1);

namespace SourceBroker\T3api\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\TypoScriptConditionProvider;

class ConditionProvider extends TypoScriptConditionProvider
{
    public function __construct()
    {
        parent::__construct();

        $this->expressionLanguageProviders = [
            ConditionFunctionsProvider::class,
        ];
    }
}
