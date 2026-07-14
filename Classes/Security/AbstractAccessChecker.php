<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Security;

use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\ExpressionLanguage\Resolver;
use SourceBroker\T3api\Service\ExpressionLanguageService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractAccessChecker
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {}

    protected function getExpressionLanguageResolver(array $additionalExpressionLanguageVariables = []): Resolver
    {
        static $expressionLanguageResolver;

        if ($expressionLanguageResolver === null) {
            $context = GeneralUtility::makeInstance(Context::class);

            $variables = array_merge(
                ExpressionLanguageService::getUserVariables($context),
                $additionalExpressionLanguageVariables
            );

            $expressionLanguageResolver = GeneralUtility::makeInstance(
                Resolver::class,
                't3api',
                $variables
            );
        }

        return $expressionLanguageResolver;
    }
}
