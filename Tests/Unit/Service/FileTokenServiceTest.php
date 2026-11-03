<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Service\FileTokenService;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class FileTokenServiceTest extends UnitTestCase
{
    private const NOW = 1800000000;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'encryption-key-of-unit-test';
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['fileToken'] = ['lifetime' => 3600];
    }

    #[Test]
    public function tokenIsValidForSameFileAndUser(): void
    {
        $token = $this->createService(5)->createToken(15);

        self::assertMatchesRegularExpression('/^\d+\.[0-9a-f]{64}$/', $token);
        self::assertTrue($this->createService(5)->isValidToken($token, 15));
        self::assertFalse($this->createService(5)->isValidToken($token, 16));
    }

    #[Test]
    public function tokenOfLoggedInUserIsNotValidForOtherUsers(): void
    {
        $token = $this->createService(5)->createToken(15);

        self::assertFalse($this->createService(6)->isValidToken($token, 15));
        self::assertFalse($this->createService(0)->isValidToken($token, 15));
    }

    #[Test]
    public function tokenOfAnonymousUserIsValidForEverybody(): void
    {
        $token = $this->createService(0)->createToken(15);

        self::assertTrue($this->createService(0)->isValidToken($token, 15));
        self::assertTrue($this->createService(5)->isValidToken($token, 15));
    }

    #[Test]
    public function tokenIsValidUntilItsLifetimeExpires(): void
    {
        $token = $this->createService(0)->createToken(15);

        self::assertTrue($this->createService(0, self::NOW + 3600)->isValidToken($token, 15));
        self::assertFalse($this->createService(0, self::NOW + 3601)->isValidToken($token, 15));
    }

    #[Test]
    #[DataProvider('invalidLifetimeProvider')]
    public function invalidLifetimeFallsBackToDefault(mixed $lifetime): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['fileToken']['lifetime'] = $lifetime;
        $token = $this->createService(0)->createToken(15);

        self::assertSame(self::NOW + 86400, (int)explode('.', $token)[0]);
    }

    public static function invalidLifetimeProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-10],
            'text' => ['one day'],
            'missing' => [null],
        ];
    }

    #[Test]
    public function lifetimeIsLimitedToMaximum(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['fileToken']['lifetime'] = PHP_INT_MAX;
        $token = $this->createService(0)->createToken(15);

        self::assertSame(self::NOW + 315360000, (int)explode('.', $token)[0]);
    }

    #[Test]
    public function tokenWithChangedExpirationIsNotValid(): void
    {
        [, $signature] = explode('.', $this->createService(0)->createToken(15));

        self::assertFalse($this->createService(0)->isValidToken((self::NOW + 999999) . '.' . $signature, 15));
    }

    #[Test]
    public function tokenWithLineBreakInExpirationIsNotValid(): void
    {
        [$expires, $signature] = explode('.', $this->createService(0)->createToken(15));

        self::assertFalse($this->createService(0)->isValidToken($expires . "\n." . $signature, 15));
    }

    #[Test]
    #[DataProvider('malformedTokenProvider')]
    public function malformedTokenIsNotValid(string $token): void
    {
        self::assertFalse($this->createService(0)->isValidToken($token, 15));
    }

    public static function malformedTokenProvider(): array
    {
        return [
            'empty' => [''],
            'without signature' => [(string)(self::NOW + 100)],
            'empty signature' => [(self::NOW + 100) . '.'],
            'not a timestamp' => ['abc.' . str_repeat('a', 64)],
        ];
    }

    #[Test]
    public function onlyGrantedFilesAreGranted(): void
    {
        $service = $this->createService(0);
        self::assertFalse($service->hasGrantedFiles());

        $service->grant(15);

        self::assertTrue($service->hasGrantedFiles());
        self::assertTrue($service->isGranted(15));
        self::assertFalse($service->isGranted(16));
    }

    #[Test]
    public function onlyTokensForResponseAreTracked(): void
    {
        $service = $this->createService(0);
        $service->grant(15);

        self::assertNull($service->getTokenForResponse(16, false));
        self::assertNull($service->getTokenForResponse(0, true));
        self::assertFalse($service->hasIssuedTokens());

        self::assertNotNull($service->getTokenForResponse(15, false));
        self::assertTrue($service->hasIssuedTokens());
        self::assertNotNull($service->getTokenForResponse(16, true));
    }

    #[Test]
    public function tokenCreatedByCustomCodeIsTracked(): void
    {
        $service = $this->createService(0);

        $service->createToken(15);

        self::assertTrue($service->hasIssuedTokens());
    }

    #[Test]
    public function resetRemovesGrantsAndIssuedTokens(): void
    {
        $service = $this->createService(0);
        $service->grant(15);
        $service->getTokenForResponse(15, false);

        $service->reset();

        self::assertFalse($service->hasGrantedFiles());
        self::assertFalse($service->hasIssuedTokens());
    }

    private function createService(int $frontendUserUid, int $timestamp = self::NOW): FileTokenService
    {
        $context = new Context();
        $context->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('@' . $timestamp)));
        if ($frontendUserUid > 0) {
            $user = self::createStub(AbstractUserAuthentication::class);
            $user->userid_column = 'uid';
            $user->user = ['uid' => $frontendUserUid];
            $context->setAspect('frontend.user', new UserAspect($user));
        }

        return new FileTokenService($context);
    }
}
