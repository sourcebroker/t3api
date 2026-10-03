<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Filter\SearchFilter;
use SourceBroker\T3api\Security\FilterAccessChecker;
use SourceBroker\T3api\Security\OperationAccessChecker;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Covers the expressions documented in Documentation/Security/Index.rst - every documented
 * variable, function and example should have a case here.
 */
class ExpressionTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/t3api'];

    #[Test]
    public function objectVariableIsAvailableInPostDenormalizeCheck(): void
    {
        $checker = $this->createOperationAccessChecker();
        $operation = $this->createOperation(securityPostDenormalize: 'object.title == "published"');

        self::assertTrue($checker->isGrantedPostDenormalize($operation, ['object' => $this->createObject('published')]));
        self::assertFalse($checker->isGrantedPostDenormalize($operation, ['object' => $this->createObject('draft')]));
    }

    #[Test]
    public function t3apiOperationVariableIsAvailableInOperationCheck(): void
    {
        $checker = $this->createOperationAccessChecker();

        self::assertTrue($checker->isGranted($this->createOperation(key: 'get', security: 't3apiOperation.getKey() == "get"')));
        self::assertTrue($checker->isGranted($this->createOperation(key: 'post', security: 't3apiOperation.getKey() == "post"')));
    }

    #[Test]
    public function collectionRequestEvaluatesOperationSecurityAndFilterCondition(): void
    {
        self::assertTrue($this->createOperationAccessChecker()->isGranted($this->createOperation(security: 'true')));
        self::assertTrue($this->createFilterAccessChecker()->isGranted(
            $this->createFilter('t3apiFilter.getProperty() == "title"')
        ));
    }

    #[Test]
    public function userVariablesFollowLoginState(): void
    {
        $checker = $this->createOperationAccessChecker();
        $operation = $this->createOperation(security: 'frontend.user.isLoggedIn');

        self::assertFalse($checker->isGranted($operation));

        $this->loginFrontendUser(12, [3, 5]);

        self::assertTrue($checker->isGranted($operation));
    }

    #[Test]
    public function operationWithoutSecurityIsGranted(): void
    {
        $checker = $this->createOperationAccessChecker();
        $operation = $this->createOperation();

        self::assertTrue($checker->isGranted($operation));
        self::assertTrue($checker->isGrantedPostDenormalize($operation));
    }

    #[Test]
    public function filterWithoutConditionIsGranted(): void
    {
        self::assertTrue($this->createFilterAccessChecker()->isGranted(
            new ApiFilter(SearchFilter::class, 'title', 'partial', [])
        ));
    }

    public static function userVariableExpressions(): array
    {
        return [
            // Examples from "User variables" section
            'logged in frontend user' => ['frontend.user.isLoggedIn', true, false],
            'frontend user from group 3' => ['3 in frontend.user.userGroupIds', true, false],
            'frontend user from group 3 or 4' => ['3 in frontend.user.userGroupIds || 4 in frontend.user.userGroupIds', true, false],
            'frontend user not from group 4' => ['4 in frontend.user.userGroupIds', false, false],
            'specific frontend user' => ['frontend.user.userId == 12', true, false],
            'other frontend user' => ['frontend.user.userId == 13', false, false],
            'anonymous frontend user id' => ['frontend.user.userId == 0', false, true],
            'frontend user group list' => ['like(frontend.user.userGroupList, "*3*")', true, false],
            'logged in backend user' => ['backend.user.isLoggedIn', true, false],
            'backend admin' => ['backend.user.isAdmin', true, false],
            'backend user from group 2' => ['2 in backend.user.userGroupIds', true, false],
            'specific backend user' => ['backend.user.userId == 1', true, false],
            'backend user group list' => ['backend.user.userGroupList == "2"', true, false],
        ];
    }

    #[Test]
    #[DataProvider('userVariableExpressions')]
    public function userVariablesAreAvailable(string $expression, bool $expectedForLoggedInUsers, bool $expectedForAnonymous): void
    {
        $checker = $this->createOperationAccessChecker();
        $operation = $this->createOperation(security: $expression);

        self::assertSame($expectedForAnonymous, $checker->isGranted($operation), 'anonymous');

        $this->loginFrontendUser(12, [3, 5]);
        $this->loginBackendUser(1, [2], true);

        self::assertSame($expectedForLoggedInUsers, $checker->isGranted($operation), 'logged in');
    }

    #[Test]
    public function groupMembershipCheckWithInDoesNotMatchSimilarGroupIds(): void
    {
        $this->loginFrontendUser(12, [13, 30]);
        $checker = $this->createOperationAccessChecker();

        // See "tip" in documentation: `in` checks exact ids, `like` on the list matches substrings
        self::assertFalse($checker->isGranted($this->createOperation(security: '3 in frontend.user.userGroupIds')));
        self::assertTrue($checker->isGranted($this->createOperation(security: 'like(frontend.user.userGroupList, "*3*")')));
    }

    #[Test]
    public function documentedVoteExampleRestrictsPatchToOwner(): void
    {
        $checker = $this->createOperationAccessChecker();
        $patch = $this->createOperation(
            security: 'frontend.user.isLoggedIn && object.getUser().getUid() == frontend.user.userId',
            securityPostDenormalize: 'object.getUser().getUid() == frontend.user.userId'
        );
        $ownVote = $this->createVote(12);

        self::assertFalse($checker->isGranted($patch, ['object' => $ownVote]), 'anonymous');

        $this->loginFrontendUser(12, [3]);

        self::assertTrue($checker->isGranted($patch, ['object' => $ownVote]), 'own vote');
        self::assertFalse($checker->isGranted($patch, ['object' => $this->createVote(99)]), 'vote of other user');
        self::assertTrue($checker->isGrantedPostDenormalize($patch, ['object' => $ownVote]), 'owner unchanged');
        self::assertFalse($checker->isGrantedPostDenormalize($patch, ['object' => $this->createVote(99)]), 'owner changed in payload');
    }

    #[Test]
    public function documentedVoteExampleRestrictsPostToLoggedInAndDeleteToAdmins(): void
    {
        $checker = $this->createOperationAccessChecker();
        $post = $this->createOperation(security: 'frontend.user.isLoggedIn');
        $delete = $this->createOperation(security: 'backend.user.isAdmin');

        self::assertFalse($checker->isGranted($post));
        self::assertFalse($checker->isGranted($delete));

        $this->loginFrontendUser(12, [3]);
        self::assertTrue($checker->isGranted($post));
        self::assertFalse($checker->isGranted($delete));

        $this->loginBackendUser(1, [2], false);
        self::assertFalse($checker->isGranted($delete), 'backend user without admin rights');

        $this->loginBackendUser(1, [2], true);
        self::assertTrue($checker->isGranted($delete));
    }

    #[Test]
    public function documentedFilterConditionEnablesFilterForBackendUsersOnly(): void
    {
        $checker = $this->createFilterAccessChecker();
        $filter = $this->createFilter('backend.user.isLoggedIn');

        self::assertFalse($checker->isGranted($filter));

        $this->loginBackendUser(1, [2], false);

        self::assertTrue($checker->isGranted($filter));
    }

    public static function t3apiAndCoreExpressions(): array
    {
        return [
            // T3api variables
            't3apiOperation' => ['t3apiOperation.getPath() == "/votes/{id}"'],
            'context' => ['context.getPropertyFromAspect("workspace", "id") == 0'],
            // TYPO3 core variables
            'applicationContext' => ['applicationContext matches "/^Testing/"'],
            'typo3.version' => ['typo3.version != ""'],
            'typo3.branch' => ['typo3.branch != ""'],
            'typo3.devIpMask' => ['typo3.devIpMask == typo3.devIpMask'],
            'date variable' => ['date.getDateTime().format("Y") >= "2024"'],
            'features' => ['features.isFeatureEnabled("not-existing-feature") == false'],
            // TYPO3 core functions
            'compatVersion()' => ['compatVersion("12.4")'],
            'like()' => ['like("t3api", "t3*")'],
            'date() before deadline' => ['date("Y-m-d") < "2999-01-01"'],
            'feature()' => ['feature("not-existing-feature") == false'],
            'traverse()' => ['traverse(object, "nested/key") == "value"'],
            // T3api functions
            'force_absolute_url()' => ['force_absolute_url("/fileadmin/a.jpg", "https://example.com") == "https://example.com/fileadmin/a.jpg"'],
        ];
    }

    #[Test]
    #[DataProvider('t3apiAndCoreExpressions')]
    public function documentedVariablesAndFunctionsAreAvailable(string $expression): void
    {
        self::assertTrue($this->createOperationAccessChecker()->isGranted(
            $this->createOperation(security: $expression, path: '/votes/{id}'),
            ['object' => ['nested' => ['key' => 'value']]]
        ));
    }

    public static function pageRelatedVariables(): array
    {
        return [
            'page' => ['page.uid > 0'],
            'tree' => ['tree.level == 0'],
            'request' => ['request.getQueryParams() == []'],
            'site' => ['site.getIdentifier() == ""'],
            'siteLanguage' => ['siteLanguage.getLanguageId() == 0'],
        ];
    }

    #[Test]
    #[DataProvider('pageRelatedVariables')]
    public function pageRelatedVariablesAreNotAvailable(string $expression): void
    {
        $this->expectException(SyntaxError::class);

        $this->createOperationAccessChecker()->isGranted($this->createOperation(security: $expression));
    }

    #[Test]
    public function variablesFromEventsAreAvailableInEveryCheck(): void
    {
        $eventDispatcher = new class () implements EventDispatcherInterface {
            public function dispatch(object $event): object
            {
                $event->setExpressionLanguageVariable('eventVariable', $event::class);

                return $event;
            }
        };
        $operationChecker = new OperationAccessChecker($eventDispatcher);
        $filterChecker = new FilterAccessChecker($eventDispatcher);

        self::assertTrue($operationChecker->isGranted(
            $this->createOperation(security: 'eventVariable matches "/BeforeOperationAccessGrantedEvent$/"')
        ));
        self::assertTrue($operationChecker->isGrantedPostDenormalize(
            $this->createOperation(securityPostDenormalize: 'eventVariable matches "/BeforeOperationAccessGrantedPostDenormalizeEvent$/"')
        ));
        self::assertTrue($filterChecker->isGranted(
            $this->createFilter('eventVariable matches "/BeforeFilterAccessGrantedEvent$/"')
        ));
    }

    private function createOperationAccessChecker(): OperationAccessChecker
    {
        return new OperationAccessChecker($this->get(EventDispatcherInterface::class));
    }

    private function createFilterAccessChecker(): FilterAccessChecker
    {
        return new FilterAccessChecker($this->get(EventDispatcherInterface::class));
    }

    private function createOperation(
        string $key = 'get',
        string $security = '',
        string $securityPostDenormalize = '',
        string $path = ''
    ): OperationInterface {
        $operation = $this->createMock(OperationInterface::class);
        $operation->method('getKey')->willReturn($key);
        $operation->method('getPath')->willReturn($path);
        $operation->method('getSecurity')->willReturn($security);
        $operation->method('getSecurityPostDenormalize')->willReturn($securityPostDenormalize);

        return $operation;
    }

    private function createFilter(string $condition): ApiFilter
    {
        return new ApiFilter(SearchFilter::class, 'title', ['name' => 'partial', 'condition' => $condition], []);
    }

    private function createObject(string $title): object
    {
        $object = new \stdClass();
        $object->title = $title;

        return $object;
    }

    private function createVote(int $userUid): object
    {
        return new class ($userUid) {
            public function __construct(private readonly int $userUid) {}

            public function getUser(): object
            {
                return new class ($this->userUid) {
                    public function __construct(private readonly int $uid) {}

                    public function getUid(): int
                    {
                        return $this->uid;
                    }
                };
            }
        };
    }

    /**
     * @param int[] $groupIds
     */
    private function loginFrontendUser(int $uid, array $groupIds): void
    {
        $frontendUser = $this->createMock(FrontendUserAuthentication::class);
        $frontendUser->userid_column = 'uid';
        $frontendUser->user = ['uid' => $uid];
        $frontendUser->userGroups = array_fill_keys($groupIds, []);

        GeneralUtility::makeInstance(Context::class)->setAspect('frontend.user', new UserAspect($frontendUser));
    }

    /**
     * @param int[] $groupIds
     */
    private function loginBackendUser(int $uid, array $groupIds, bool $isAdmin): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser->method('isAdmin')->willReturn($isAdmin);
        $backendUser->userid_column = 'uid';
        $backendUser->user = ['uid' => $uid];
        $backendUser->userGroupsUID = $groupIds;

        GeneralUtility::makeInstance(Context::class)->setAspect('backend.user', new UserAspect($backendUser));
    }
}
