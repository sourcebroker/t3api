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

    /**
     * Built once per checker without any variables - variables differ for every check (`object`,
     * `t3apiOperation`, `t3apiFilter`, event-provided ones) and are passed to each evaluation.
     */
    protected ?Resolver $expressionLanguageResolver = null;

    protected function evaluateExpression(string $expression, array $additionalExpressionLanguageVariables = []): bool
    {
        $this->expressionLanguageResolver ??= GeneralUtility::makeInstance(Resolver::class, 't3api', []);

        return (bool)$this->expressionLanguageResolver->evaluate(
            $expression,
            array_merge(
                ExpressionLanguageService::getUserVariables(GeneralUtility::makeInstance(Context::class)),
                $additionalExpressionLanguageVariables
            )
        );
    }
}
