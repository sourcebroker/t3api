<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\ExpressionLanguage\Resolver;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExpressionLanguageService
{
    public static function getT3apiExpressionLanguage(): ExpressionLanguage
    {
        return GeneralUtility::makeInstance(Resolver::class, 't3api', [])->getExpressionLanguage();
    }
}
