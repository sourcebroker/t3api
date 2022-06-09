<?php

declare(strict_types=1);
namespace SourceBroker\T3api\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

class T3apiCoreProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            T3apiCoreFunctionsProvider::class,
        ];
    }
}
