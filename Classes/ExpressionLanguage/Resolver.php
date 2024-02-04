<?php

declare(strict_types=1);

namespace SourceBroker\T3api\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver as BaseResolver;

/**
 * Extends TYPO3's `TYPO3\CMS\Core\ExpressionLanguage\Resolver` to build expression language context in the same way as
 * in TYPO3 core but allow to get it from outside by public getter. This class should be removed if in future TYPO3 versions
 * getting expression language will be part of core API.
 */
class Resolver extends BaseResolver
{
    /**
     * @return ExpressionLanguage
     * @internal
     */
    public function getExpressionLanguage(): ExpressionLanguage
    {
        $reflection = new \ReflectionClass(BaseResolver::class);
        $property = $reflection->getProperty('expressionLanguage');
        $property->setAccessible(true);
        return $property->getValue($this);
    }

    public function getExpressionLanguageVariables(): array
    {
        $reflection = new \ReflectionClass(BaseResolver::class);
        $property = $reflection->getProperty('expressionLanguageVariables');
        $property->setAccessible(true);
        return $property->getValue($this);
    }
}
