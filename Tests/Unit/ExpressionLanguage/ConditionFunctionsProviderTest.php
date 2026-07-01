<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\ExpressionLanguage;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\ExpressionLanguage\ConditionFunctionsProvider;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\CMS\Core\ExpressionLanguage\FunctionsProvider\Typo3ConditionFunctionsProvider;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ConditionFunctionsProviderTest extends UnitTestCase
{
    /**
     * @test
     */
    #[Test]
    public function getFunctionsReturnsTypo3ConditionFunctions(): void
    {
        $expectedFunctionNames = array_map(
            static fn(ExpressionFunction $function): string => $function->getName(),
            GeneralUtility::makeInstance(Typo3ConditionFunctionsProvider::class)->getFunctions()
        );

        $actualFunctionNames = array_map(
            static fn(ExpressionFunction $function): string => $function->getName(),
            (new ConditionFunctionsProvider())->getFunctions()
        );

        self::assertSame($expectedFunctionNames, $actualFunctionNames);
    }

    /**
     * @test
     */
    #[Test]
    public function functionsCanBeEvaluatedByExpressionLanguage(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getIdentifier')->willReturn('t3api-test');

        $expressionLanguage = new ExpressionLanguage(null, [
            new ConditionFunctionsProvider(),
        ]);

        self::assertSame(
            't3api-test',
            $expressionLanguage->evaluate('site("identifier")', ['site' => $site])
        );
    }

    /**
     * @test
     */
    #[Test]
    public function getFunctionsUsesTypo3ProviderResolvedByGeneralUtility(): void
    {
        $delegatedProvider = self::createStub(Typo3ConditionFunctionsProvider::class);
        $delegatedProvider->method('getFunctions')->willReturn([
            new ExpressionFunction(
                'delegated_condition_function',
                static fn(): string => 'null',
                static fn(array $arguments): string => 'delegated'
            ),
        ]);

        GeneralUtility::addInstance(Typo3ConditionFunctionsProvider::class, $delegatedProvider);

        $expressionLanguage = new ExpressionLanguage(null, [
            new ConditionFunctionsProvider(),
        ]);

        self::assertSame('delegated', $expressionLanguage->evaluate('delegated_condition_function()'));
    }
}
