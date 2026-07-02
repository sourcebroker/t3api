<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Service\RouteService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class RouteServiceTest extends UnitTestCase
{
    public static function stripSiteBasePathPrefixDataProvider(): array
    {
        return [
            'site installed at root - path returned unchanged' => [
                '/de/',
                '/',
                '/de/',
            ],
            'site installed in a subdirectory - subdirectory is stripped' => [
                '/shop/de/',
                '/shop/',
                '/de/',
            ],
            'site installed in a subdirectory - default language equals site base' => [
                '/shop/',
                '/shop/',
                '/',
            ],
            'path does not start with the site base - returned unchanged' => [
                '/de/',
                '/shop/',
                '/de/',
            ],
        ];
    }

    #[Test]
    #[DataProvider('stripSiteBasePathPrefixDataProvider')]
    public function stripSiteBasePathPrefixReturnsExpectedPath(
        string $path,
        string $siteBasePath,
        string $expected
    ): void {
        self::assertSame($expected, RouteService::stripSiteBasePathPrefix($path, $siteBasePath));
    }
}
