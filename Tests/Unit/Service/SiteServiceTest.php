<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Service\SiteService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Covers `SiteService::getResolvedByTypo3()` directly (via reflection) instead of `getCurrent()`,
 * which memoizes its result in a function-local static variable for the lifetime of the process.
 */
class SiteServiceTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        parent::tearDown();
    }

    #[Test]
    public function getResolvedByTypo3ReturnsSiteAttributeOfGlobalRequest(): void
    {
        $site = new Site('test', 1, ['base' => 'https://example.com/']);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('site', $site);

        self::assertSame($site, $this->callGetResolvedByTypo3());
    }

    #[Test]
    public function getResolvedByTypo3ReturnsNullOnCliWithoutRequestUrl(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        self::assertNull($this->callGetResolvedByTypo3());
    }

    private function callGetResolvedByTypo3(): mixed
    {
        return (new \ReflectionMethod(SiteService::class, 'getResolvedByTypo3'))->invoke(null);
    }
}
