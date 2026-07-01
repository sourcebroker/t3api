<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\ExpressionLanguage;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\ExpressionLanguage\T3apiCoreFunctionsProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class T3apiCoreFunctionsProviderTest extends UnitTestCase
{
    /**
     * @test
     */
    #[Test]
    public function forceAbsoluteUrlExpressionReturnsAbsolutePublicUrl(): void
    {
        $object = new class () {
            public function getPublicUrl(): string
            {
                return '/fileadmin/example.jpg';
            }
        };
        $context = new class () {
            public function getAttribute(string $name): string
            {
                T3apiCoreFunctionsProviderTest::assertSame('TYPO3_SITE_URL', $name);

                return 'https://example.test/';
            }
        };
        $expressionLanguage = new ExpressionLanguage(null, [
            new T3apiCoreFunctionsProvider(),
        ]);

        self::assertSame(
            'https://example.test/fileadmin/example.jpg',
            $expressionLanguage->evaluate(
                'force_absolute_url(object.getPublicUrl(), context.getAttribute(\'TYPO3_SITE_URL\'))',
                [
                    'object' => $object,
                    'context' => $context,
                ]
            )
        );
    }
}
