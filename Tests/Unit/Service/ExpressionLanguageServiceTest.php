<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Service\ExpressionLanguageService;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ExpressionLanguageServiceTest extends UnitTestCase
{
    /**
     * TYPO3's `Context::hasAspect()` always answers `true` for `backend.user`/`frontend.user`
     * (it lazily creates an empty `UserAspect` on first access) - so, unlike other aspects, these
     * two never actually hit the `null` branch of the guard at runtime. The guard is kept as-is
     * since this method only relocates the pre-existing duplicated logic, not changes it.
     */
    #[Test]
    public function buildsDefaultLoggedOutUserVariablesWhenNoUserAspectIsExplicitlySet(): void
    {
        $variables = ExpressionLanguageService::getUserVariables(new Context());

        self::assertFalse($variables['backend']->user->isLoggedIn);
        self::assertFalse($variables['frontend']->user->isLoggedIn);
    }

    #[Test]
    public function buildsBackendUserVariableFromBackendUserAspect(): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser->method('isAdmin')->willReturn(true);
        $backendUser->userid_column = 'uid';
        $backendUser->user = ['uid' => 7];
        $backendUser->userGroupsUID = [1, 2];

        $context = new Context();
        $context->setAspect('backend.user', new UserAspect($backendUser));

        $variables = ExpressionLanguageService::getUserVariables($context);

        self::assertSame(7, $variables['backend']->user->userId);
        self::assertTrue($variables['backend']->user->isAdmin);
        self::assertTrue($variables['backend']->user->isLoggedIn);
        self::assertSame('1,2', $variables['backend']->user->userGroupList);
    }

    #[Test]
    public function buildsFrontendUserVariableFromFrontendUserAspect(): void
    {
        $frontendUser = $this->createMock(AbstractUserAuthentication::class);
        $frontendUser->userid_column = 'uid';
        $frontendUser->user = ['uid' => 3];

        $context = new Context();
        $context->setAspect('frontend.user', new UserAspect($frontendUser));

        $variables = ExpressionLanguageService::getUserVariables($context);

        self::assertSame(3, $variables['frontend']->user->userId);
        self::assertTrue($variables['frontend']->user->isLoggedIn);
    }
}
