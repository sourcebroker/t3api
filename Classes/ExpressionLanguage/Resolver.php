<?php

declare(strict_types=1);

namespace SourceBroker\T3api\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\CMS\Core\ExpressionLanguage\DefaultProvider;
use TYPO3\CMS\Core\ExpressionLanguage\ProviderConfigurationLoader;
use TYPO3\CMS\Core\ExpressionLanguage\ProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Resolver
{
    private ExpressionLanguage $expressionLanguage;
    private array $expressionLanguageVariables;

    public function __construct(string $context, array $variables)
    {
        $functionProviderInstances = [];
        $providers = GeneralUtility::makeInstance(ProviderConfigurationLoader::class)->getExpressionLanguageProviders()[$context] ?? [];
        array_unshift($providers, DefaultProvider::class);
        $providers = array_unique($providers);
        $functionProviders = [];
        $generalVariables = [];
        foreach ($providers as $provider) {
            /** @var ProviderInterface $providerInstance */
            $providerInstance = GeneralUtility::makeInstance($provider);
            $functionProviders[] = $providerInstance->getExpressionLanguageProviders();
            $generalVariables[] = $providerInstance->getExpressionLanguageVariables();
        }
        $functionProviders = array_merge(...$functionProviders);
        $generalVariables = array_replace_recursive(...$generalVariables);
        $this->expressionLanguageVariables = array_replace_recursive($generalVariables, $variables);
        foreach ($functionProviders as $functionProvider) {
            /** @var ExpressionFunctionProviderInterface[] $functionProviderInstances */
            $functionProviderInstances[] = GeneralUtility::makeInstance($functionProvider);
        }
        $this->expressionLanguage = new ExpressionLanguage(null, $functionProviderInstances);
    }

    /**
     * @internal
     */
    public function getExpressionLanguage(): ExpressionLanguage
    {
        return $this->expressionLanguage;
    }

    public function evaluate(string $expression, array $contextVariables = []): mixed
    {
        return $this->expressionLanguage->evaluate(
            $expression,
            array_replace($this->expressionLanguageVariables, $contextVariables)
        );
    }

    public function compile(string $condition): string
    {
        return $this->expressionLanguage->compile($condition, array_keys($this->expressionLanguageVariables));
    }
}
