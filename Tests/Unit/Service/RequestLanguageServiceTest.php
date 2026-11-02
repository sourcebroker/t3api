<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Exception\BadRequestException;
use SourceBroker\T3api\Service\RequestLanguageService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class RequestLanguageServiceTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader'] = 'X-Locale';
        $this->site = new Site('test', 1, [
            'base' => 'https://example.com/',
            'languages' => [
                ['languageId' => 0, 'title' => 'English', 'base' => '/', 'locale' => 'en_US.UTF-8'],
                ['languageId' => 1, 'title' => 'German', 'base' => '/de/', 'locale' => 'de_DE.UTF-8'],
                ['languageId' => 2, 'title' => 'Polish', 'base' => '/pl/', 'locale' => 'pl_PL.UTF-8', 'enabled' => false],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['BE_USER']);
        parent::tearDown();
    }

    #[Test]
    #[DataProvider('validHeaderProvider')]
    public function headerWithAvailableLanguageSwitchesLanguage(string $header, int $expectedLanguageId): void
    {
        $request = (new RequestLanguageService())->withLanguageFromHeader($this->createRequest($header));

        self::assertNull($request->getAttribute(RequestLanguageService::ATTRIBUTE_LANGUAGE_HEADER_EXCEPTION));
        self::assertSame($expectedLanguageId, $request->getAttribute('language')->getLanguageId());
        self::assertSame(
            $expectedLanguageId,
            GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id')
        );
    }

    public static function validHeaderProvider(): array
    {
        return [
            'enabled language' => ['1', 1],
            'surrounding whitespace' => [' 1 ', 1],
            'language from URL' => ['0', 0],
        ];
    }

    #[Test]
    #[DataProvider('invalidHeaderProvider')]
    public function headerWithUnavailableLanguageIsRejected(string $header): void
    {
        $request = (new RequestLanguageService())->withLanguageFromHeader($this->createRequest($header));

        $exception = $request->getAttribute(RequestLanguageService::ATTRIBUTE_LANGUAGE_HEADER_EXCEPTION);
        self::assertInstanceOf(BadRequestException::class, $exception);
        self::assertSame(1793641754, $exception->getCode());
        self::assertSame(0, $request->getAttribute('language')->getLanguageId());
    }

    public static function invalidHeaderProvider(): array
    {
        return [
            'not existing language' => ['99'],
            'negative ID' => ['-1'],
            'not a number' => ['abc'],
            'number with suffix' => ['1abc'],
            'disabled language' => ['2'],
        ];
    }

    #[Test]
    public function disabledLanguageIsAvailableForBackendUserWithAccess(): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser->expects(self::once())->method('checkLanguageAccess')->with(2)->willReturn(true);
        $GLOBALS['BE_USER'] = $backendUser;

        $request = (new RequestLanguageService())->withLanguageFromHeader($this->createRequest('2'));

        self::assertNull($request->getAttribute(RequestLanguageService::ATTRIBUTE_LANGUAGE_HEADER_EXCEPTION));
        self::assertSame(2, $request->getAttribute('language')->getLanguageId());
    }

    #[Test]
    public function disabledLanguageIsRejectedForBackendUserWithoutAccess(): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser->expects(self::once())->method('checkLanguageAccess')->with(2)->willReturn(false);
        $GLOBALS['BE_USER'] = $backendUser;

        $request = (new RequestLanguageService())->withLanguageFromHeader($this->createRequest('2'));

        self::assertInstanceOf(
            BadRequestException::class,
            $request->getAttribute(RequestLanguageService::ATTRIBUTE_LANGUAGE_HEADER_EXCEPTION)
        );
    }

    #[Test]
    public function requestWithoutHeaderIsNotChanged(): void
    {
        $request = (new ServerRequest('https://example.com/_api/items'))
            ->withAttribute('site', $this->site)
            ->withAttribute('language', $this->site->getDefaultLanguage());

        self::assertSame($request, (new RequestLanguageService())->withLanguageFromHeader($request));
    }

    #[Test]
    public function requestWithEmptyHeaderIsNotChanged(): void
    {
        $request = $this->createRequest(' ');

        self::assertSame($request, (new RequestLanguageService())->withLanguageFromHeader($request));
    }

    private function createRequest(string $header): ServerRequest
    {
        return (new ServerRequest('https://example.com/_api/items'))
            ->withHeader('X-Locale', $header)
            ->withAttribute('site', $this->site)
            ->withAttribute('language', $this->site->getDefaultLanguage());
    }
}
