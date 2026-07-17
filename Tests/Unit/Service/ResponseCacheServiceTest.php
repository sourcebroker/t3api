<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\Pagination;
use SourceBroker\T3api\Domain\Model\ResponseCacheSettings;
use SourceBroker\T3api\ExpressionLanguage\Resolver;
use SourceBroker\T3api\Service\ResponseCacheService;
use SourceBroker\T3api\Tests\Unit\Domain\Model\PaginationTest;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ResponseCacheServiceTest extends UnitTestCase
{
    private MockObject&FrontendInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->createMock(FrontendInterface::class);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/_api/books'))
            ->withAttribute('site', new Site('site-one', 1, ['base' => 'https://example.com/', 'languages' => []]))
            ->withAttribute('language', new SiteLanguage(0, 'en_US.UTF-8', new Uri('https://example.com/'), []));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        parent::tearDown();
    }

    #[Test]
    public function returnsNullForNonGetRequests(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $request = Request::create('https://example.com/_api/books', 'POST');

        self::assertNull($service->buildEntryIdentifier($this->createOperation([]), [], $request));
    }

    #[Test]
    public function returnsNullWhenCacheIsDisabled(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $request = Request::create('https://example.com/_api/books', 'GET');

        self::assertNull($service->buildEntryIdentifier($this->createOperation(null), [], $request));
    }

    #[Test]
    public function falsyFilterValueProducesDistinctIdentifier(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([]);

        self::assertNotSame(
            $service->buildEntryIdentifier($operation, [], Request::create('https://example.com/_api/books?title=0', 'GET')),
            $service->buildEntryIdentifier($operation, [], Request::create('https://example.com/_api/books', 'GET'))
        );
    }

    #[Test]
    public function unknownParametersDoNotInfluenceTheIdentifier(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([]);

        $withUnknownParam = $service->buildEntryIdentifier(
            $operation,
            [],
            Request::create('https://example.com/_api/books?utm_source=foo', 'GET')
        );
        $withoutParams = $service->buildEntryIdentifier(
            $operation,
            [],
            Request::create('https://example.com/_api/books', 'GET')
        );

        self::assertNotNull($withoutParams);
        self::assertSame($withoutParams, $withUnknownParam);
    }

    #[Test]
    public function paginationAndFilterParametersInfluenceTheIdentifier(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([]);

        $pageOne = $service->buildEntryIdentifier(
            $operation,
            [],
            Request::create('https://example.com/_api/books?page=1', 'GET')
        );
        $pageTwo = $service->buildEntryIdentifier(
            $operation,
            [],
            Request::create('https://example.com/_api/books?page=2', 'GET')
        );
        $filtered = $service->buildEntryIdentifier(
            $operation,
            [],
            Request::create('https://example.com/_api/books?title=foo', 'GET')
        );

        self::assertNotSame($pageOne, $pageTwo);
        self::assertNotSame($pageOne, $filtered);
    }

    #[Test]
    public function pathInfluencesTheIdentifier(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([]);

        self::assertNotSame(
            $service->buildEntryIdentifier($operation, [], Request::create('https://example.com/_api/books', 'GET')),
            $service->buildEntryIdentifier($operation, [], Request::create('https://example.com/_api/novels', 'GET'))
        );
    }

    #[Test]
    public function siteIdentifierInfluencesTheIdentifier(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([]);
        $request = Request::create('https://example.com/_api/books', 'GET');

        $siteOneIdentifier = $service->buildEntryIdentifier($operation, [], $request);

        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute(
            'site',
            new Site('site-two', 2, ['base' => 'https://other.example.com/', 'languages' => []])
        );

        self::assertNotSame($siteOneIdentifier, $service->buildEntryIdentifier($operation, [], $request));
    }

    #[Test]
    public function languageIdInfluencesTheIdentifier(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([]);
        $request = Request::create('https://example.com/_api/books', 'GET');

        $defaultLanguageIdentifier = $service->buildEntryIdentifier($operation, [], $request);

        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute(
            'language',
            new SiteLanguage(1, 'de_DE.UTF-8', new Uri('https://example.com/de/'), [])
        );

        self::assertNotSame($defaultLanguageIdentifier, $service->buildEntryIdentifier($operation, [], $request));
    }

    /**
     * `context` (a `getPropertyFromAspect`-capable TYPO3 `Context`) is a provider-level
     * expression variable, not one `getConditionVariables()` adds itself - the double resolver
     * is therefore given it directly, the same way the real `T3apiCoreProvider` would.
     */
    #[Test]
    public function aspectBasedIdentifierExpressionVariesByContextAspectValue(): void
    {
        $operation = $this->createOperation([
            'identifierExpressions' => ["context.getPropertyFromAspect('frontend.user', 'isLoggedIn', '')"],
        ]);
        $request = Request::create('https://example.com/_api/books', 'GET');

        $loggedOutContext = new Context();
        $this->registerPlainSymfonyExpressionResolver(['context' => $loggedOutContext]);
        $loggedOutIdentifier = (new ResponseCacheService($this->cache, $loggedOutContext))
            ->buildEntryIdentifier($operation, [], $request);

        // `UserAspect` is declared `final` and cannot be doubled - build a real instance backed
        // by a mocked user authentication object instead, so it reports a different value.
        $loggedInUser = $this->createMock(AbstractUserAuthentication::class);
        $loggedInUser->userid_column = 'uid';
        $loggedInUser->user = ['uid' => 1];

        $loggedInContext = new Context();
        $loggedInContext->setAspect('frontend.user', new UserAspect($loggedInUser));
        $this->registerPlainSymfonyExpressionResolver(['context' => $loggedInContext]);
        $loggedInIdentifier = (new ResponseCacheService($this->cache, $loggedInContext))
            ->buildEntryIdentifier($operation, [], $request);

        self::assertNotSame($loggedOutIdentifier, $loggedInIdentifier);
    }

    #[Test]
    public function identifierExpressionMakesRequestsWithDifferentHeaderValuesGetDifferentIdentifiers(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([
            'identifierExpressions' => ["request.headers.get('X-App-Version')"],
        ]);

        $requestWithVersionOne = Request::create('https://example.com/_api/books', 'GET');
        $requestWithVersionOne->headers->set('X-App-Version', '1');
        $requestWithVersionTwo = Request::create('https://example.com/_api/books', 'GET');
        $requestWithVersionTwo->headers->set('X-App-Version', '2');

        self::assertNotSame(
            $service->buildEntryIdentifier($operation, [], $requestWithVersionOne),
            $service->buildEntryIdentifier($operation, [], $requestWithVersionTwo)
        );
    }

    #[Test]
    public function identifierExpressionProducesSameIdentifierForSameHeaderValue(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([
            'identifierExpressions' => ["request.headers.get('X-App-Version')"],
        ]);

        $firstRequest = Request::create('https://example.com/_api/books', 'GET');
        $firstRequest->headers->set('X-App-Version', '1');
        $secondRequest = Request::create('https://example.com/_api/books', 'GET');
        $secondRequest->headers->set('X-App-Version', '1');

        self::assertSame(
            $service->buildEntryIdentifier($operation, [], $firstRequest),
            $service->buildEntryIdentifier($operation, [], $secondRequest)
        );
    }

    #[Test]
    public function twoIdentifierExpressionsBothInfluenceTheIdentifier(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([
            'identifierExpressions' => [
                "request.headers.get('X-App-Version')",
                "request.headers.get('X-Tenant')",
            ],
        ]);

        $baseline = Request::create('https://example.com/_api/books', 'GET');
        $baseline->headers->set('X-App-Version', '1');
        $baseline->headers->set('X-Tenant', 'acme');

        $differentSecondExpressionOnly = Request::create('https://example.com/_api/books', 'GET');
        $differentSecondExpressionOnly->headers->set('X-App-Version', '1');
        $differentSecondExpressionOnly->headers->set('X-Tenant', 'other');

        self::assertNotSame(
            $service->buildEntryIdentifier($operation, [], $baseline),
            $service->buildEntryIdentifier($operation, [], $differentSecondExpressionOnly)
        );
    }

    #[Test]
    public function identifierExpressionMakesRequestsWithDifferentRouteParametersGetDifferentIdentifiers(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([
            'identifierExpressions' => ["route['id']"],
        ]);
        $request = Request::create('https://example.com/_api/books/5', 'GET');

        self::assertNotSame(
            $service->buildEntryIdentifier($operation, ['id' => '5'], $request),
            $service->buildEntryIdentifier($operation, ['id' => '6'], $request)
        );
    }

    #[Test]
    public function emptyIdentifierExpressionsArrayLeavesIdentifierUnaffected(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $request = Request::create('https://example.com/_api/books', 'GET');

        self::assertSame(
            $service->buildEntryIdentifier($this->createOperation([]), [], $request),
            $service->buildEntryIdentifier($this->createOperation(['identifierExpressions' => []]), [], $request)
        );
    }

    #[Test]
    public function brokenIdentifierExpressionFailsClosedAndLogsError(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new ResponseCacheService($this->cache, new Context());
        $service->setLogger($logger);
        $request = Request::create('https://example.com/_api/books', 'GET');
        $operation = $this->createOperation([
            'identifierExpressions' => ['thisFunctionDoesNotExist()'],
        ]);

        self::assertNull($service->buildEntryIdentifier($operation, [], $request));
    }

    /**
     * The first expression evaluates fine; the second fails - the whole identifier must still
     * fail closed, not just fall back to the part that succeeded.
     */
    #[Test]
    public function secondIdentifierExpressionFailingFailsClosedAndLogsError(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new ResponseCacheService($this->cache, new Context());
        $service->setLogger($logger);
        $request = Request::create('https://example.com/_api/books', 'GET');
        $operation = $this->createOperation([
            'identifierExpressions' => [
                "request.headers.get('X-App-Version')",
                'thisFunctionDoesNotExist()',
            ],
        ]);

        self::assertNull($service->buildEntryIdentifier($operation, [], $request));
    }

    #[Test]
    public function getReturnsNullOnCacheBackendFailure(): void
    {
        $this->cache->method('get')->willThrowException(new \RuntimeException('backend down'));
        $service = new ResponseCacheService($this->cache, new Context());

        self::assertNull($service->get('abc'));
    }

    #[Test]
    public function storeSwallowsCacheBackendFailure(): void
    {
        $this->cache->method('set')->willThrowException(new \RuntimeException('backend down'));
        $service = new ResponseCacheService($this->cache, new Context());

        $service->store('abc', '{}', [], 60);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function clearCachePostProcFlushesEverythingOnCacheCmdAll(): void
    {
        $this->cache->expects(self::once())->method('flush');
        (new ResponseCacheService($this->cache, new Context()))->clearCachePostProc(['cacheCmd' => 'all']);
    }

    #[Test]
    public function clearCachePostProcFlushesByGivenTags(): void
    {
        $this->cache->expects(self::once())->method('flushByTags')->with(['tx_foo', 'tx_foo_5']);
        (new ResponseCacheService($this->cache, new Context()))
            ->clearCachePostProc(['tags' => ['tx_foo' => 1, 'tx_foo_5' => 1]]);
    }

    #[Test]
    public function returnsNullWhenSecuredFilterParameterIsPresentInRequest(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $request = Request::create('https://example.com/_api/books?title=foo', 'GET');
        $operation = $this->createOperation([], filters: [$this->createSecuredTitleFilter()]);

        self::assertNull($service->buildEntryIdentifier($operation, [], $request));
    }

    #[Test]
    public function buildsIdentifierWhenSecuredFilterParameterIsAbsentFromRequest(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $request = Request::create('https://example.com/_api/books', 'GET');
        $operation = $this->createOperation([], filters: [$this->createSecuredTitleFilter()]);

        self::assertNotNull($service->buildEntryIdentifier($operation, [], $request));
    }

    #[Test]
    public function emptyConditionsAllowBothReadAndWrite(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $request = Request::create('https://example.com/_api/books', 'GET');
        $operation = $this->createOperation([]);

        self::assertTrue($service->isReadAllowed($operation, [], $request));
        self::assertTrue($service->isWriteAllowed($operation, [], $request));
    }

    #[Test]
    public function isReadAllowedReturnsFalseWhenReadConditionEvaluatesToFalse(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $service = new ResponseCacheService($this->cache, new Context());
        $request = Request::create('https://example.com/_api/books', 'GET');
        $operation = $this->createOperation(['readCondition' => 'false']);

        self::assertFalse($service->isReadAllowed($operation, [], $request));
    }

    #[Test]
    public function isWriteAllowedReturnsFalseWhenWriteConditionEvaluatesToFalse(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $service = new ResponseCacheService($this->cache, new Context());
        $request = Request::create('https://example.com/_api/books', 'GET');
        $operation = $this->createOperation(['writeCondition' => 'false']);

        self::assertFalse($service->isWriteAllowed($operation, [], $request));
    }

    #[Test]
    public function readConditionCanInspectTheSymfonyRequest(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([
            'readCondition' => "request.query.get('refresh') == null",
        ]);

        self::assertFalse(
            $service->isReadAllowed($operation, [], Request::create('https://example.com/_api/books?refresh=1', 'GET'))
        );
        self::assertTrue(
            $service->isReadAllowed($operation, [], Request::create('https://example.com/_api/books', 'GET'))
        );
    }

    #[Test]
    public function readConditionCanInspectTheMatchedRouteParameter(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $service = new ResponseCacheService($this->cache, new Context());
        $operation = $this->createOperation([
            'readCondition' => "route['id'] == '5'",
        ]);
        $request = Request::create('https://example.com/_api/books/5', 'GET');

        self::assertTrue($service->isReadAllowed($operation, ['id' => '5'], $request));
        self::assertFalse($service->isReadAllowed($operation, ['id' => '6'], $request));
    }

    /**
     * The `route` variable never carries `_route` - its value is the spl_object_hash-based route
     * name, nondeterministic across processes, so expressions using it would break identifier
     * sharing. All other matched parameters pass through untouched. Exercised directly against
     * the protected `getConditionVariables()` builder rather than through an expression, since
     * the `in`/`not in` operators test array *value* membership, not key membership, and are
     * therefore unsuitable for asserting a key is absent.
     */
    #[Test]
    public function routeVariableExcludesRouteNameKey(): void
    {
        $service = new ResponseCacheService($this->cache, new Context());
        $reflectionMethod = new \ReflectionMethod($service, 'getConditionVariables');
        $reflectionMethod->setAccessible(true);

        $variables = $reflectionMethod->invoke(
            $service,
            $this->createOperation([]),
            ['id' => '5', '_route' => 'books_get'],
            Request::create('https://example.com/_api/books/5', 'GET')
        );

        self::assertSame(['id' => '5'], $variables['route']);
    }

    #[Test]
    public function brokenConditionFailsClosedAndLogsError(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new ResponseCacheService($this->cache, new Context());
        $service->setLogger($logger);
        $request = Request::create('https://example.com/_api/books', 'GET');
        $operation = $this->createOperation([
            'readCondition' => 'thisFunctionDoesNotExist()',
        ]);

        self::assertFalse($service->isReadAllowed($operation, [], $request));
    }

    /**
     * Mirrors `aspectBasedIdentifierExpressionVariesByContextAspectValue()` above: the same
     * Context-aspect-driven expression that produces an empty string for an anonymous visitor and
     * a per-user tag for a logged-in one - the documented idiom for conditional tagging (see
     * `cache.tagExpressions`).
     */
    #[Test]
    public function evaluateStoreTagExpressionsSkipsEmptyResultAndKeepsResolvedTag(): void
    {
        $this->cache->method('isValidTag')->willReturn(true);
        $operation = $this->createOperation([]);
        $request = Request::create('https://example.com/_api/books', 'GET');
        $tagExpressions = [
            "context.getPropertyFromAspect('frontend.user', 'isLoggedIn', '') "
            . "? 'fe_user_' ~ context.getPropertyFromAspect('frontend.user', 'id', '') : ''",
        ];

        $loggedOutContext = new Context();
        $this->registerPlainSymfonyExpressionResolver(['context' => $loggedOutContext]);

        self::assertSame(
            [],
            (new ResponseCacheService($this->cache, $loggedOutContext))
                ->evaluateStoreTagExpressions($tagExpressions, $operation, [], $request)
        );

        $loggedInUser = $this->createMock(AbstractUserAuthentication::class);
        $loggedInUser->userid_column = 'uid';
        $loggedInUser->user = ['uid' => 1];

        $loggedInContext = new Context();
        $loggedInContext->setAspect('frontend.user', new UserAspect($loggedInUser));
        $this->registerPlainSymfonyExpressionResolver(['context' => $loggedInContext]);

        self::assertSame(
            ['fe_user_1'],
            (new ResponseCacheService($this->cache, $loggedInContext))
                ->evaluateStoreTagExpressions($tagExpressions, $operation, [], $request)
        );
    }

    #[Test]
    public function evaluateStoreTagExpressionsCanUseMatchedRouteParameter(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $this->cache->method('isValidTag')->willReturn(true);
        $service = new ResponseCacheService($this->cache, new Context());

        self::assertSame(
            ['tx_myext_domain_model_recipe_42'],
            $service->evaluateStoreTagExpressions(
                ["'tx_myext_domain_model_recipe_' ~ route['recipeId']"],
                $this->createOperation([]),
                ['recipeId' => '42', '_route' => 'recipes_favorite'],
                Request::create('https://example.com/_api/recipes/42/favorite', 'POST')
            )
        );
    }

    /**
     * `object` is not part of the standard tag-expression variable set - `OperationResponseCache`
     * adds it explicitly, via `$additionalVariables`, only for `memberTagExpressions` (see
     * `evaluateMemberTagExpressions()`). This exercises the plumbing directly: an `object` given via
     * `$additionalVariables` is visible to the expression.
     */
    #[Test]
    public function evaluateStoreTagExpressionsMakesAdditionalVariablesAvailableToTheExpression(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $this->cache->method('isValidTag')->willReturn(true);
        $service = new ResponseCacheService($this->cache, new Context());

        self::assertSame(
            ['author_7'],
            $service->evaluateStoreTagExpressions(
                ["'author_' ~ object.getAuthorUid()"],
                $this->createOperation([]),
                [],
                Request::create('https://example.com/_api/books', 'GET'),
                ['object' => new class () {
                    public function getAuthorUid(): int
                    {
                        return 7;
                    }
                }]
            )
        );
    }

    #[Test]
    public function evaluateStoreTagExpressionsFailsClosedAndLogsErrorOnEvaluationFailure(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new ResponseCacheService($this->cache, new Context());
        $service->setLogger($logger);

        self::assertNull(
            $service->evaluateStoreTagExpressions(
                ['thisFunctionDoesNotExist()'],
                $this->createOperation([]),
                [],
                Request::create('https://example.com/_api/books', 'GET')
            )
        );
    }

    #[Test]
    public function evaluateStoreTagExpressionsFailsClosedAndLogsErrorOnInvalidTag(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $this->cache->method('isValidTag')->willReturn(false);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new ResponseCacheService($this->cache, new Context());
        $service->setLogger($logger);

        self::assertNull(
            $service->evaluateStoreTagExpressions(
                ["'invalid tag with spaces'"],
                $this->createOperation([]),
                [],
                Request::create('https://example.com/_api/books', 'GET')
            )
        );
    }

    #[Test]
    public function evaluateFlushTagExpressionsSkipsEmptyResultAndKeepsResolvedTag(): void
    {
        $this->cache->method('isValidTag')->willReturn(true);
        $operation = $this->createOperation([]);
        $request = Request::create('https://example.com/_api/books', 'GET');
        $tagExpressions = [
            "context.getPropertyFromAspect('frontend.user', 'isLoggedIn', '') "
            . "? 'fe_user_' ~ context.getPropertyFromAspect('frontend.user', 'id', '') : ''",
        ];

        $loggedOutContext = new Context();
        $this->registerPlainSymfonyExpressionResolver(['context' => $loggedOutContext]);

        self::assertSame(
            [],
            (new ResponseCacheService($this->cache, $loggedOutContext))
                ->evaluateFlushTagExpressions($tagExpressions, $operation, [], $request)
        );

        $loggedInUser = $this->createMock(AbstractUserAuthentication::class);
        $loggedInUser->userid_column = 'uid';
        $loggedInUser->user = ['uid' => 1];

        $loggedInContext = new Context();
        $loggedInContext->setAspect('frontend.user', new UserAspect($loggedInUser));
        $this->registerPlainSymfonyExpressionResolver(['context' => $loggedInContext]);

        self::assertSame(
            ['fe_user_1'],
            (new ResponseCacheService($this->cache, $loggedInContext))
                ->evaluateFlushTagExpressions($tagExpressions, $operation, [], $request)
        );
    }

    #[Test]
    public function evaluateFlushTagExpressionsCanUseMatchedRouteParameter(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $this->cache->method('isValidTag')->willReturn(true);
        $service = new ResponseCacheService($this->cache, new Context());

        self::assertSame(
            ['tx_myext_domain_model_recipe_42'],
            $service->evaluateFlushTagExpressions(
                ["'tx_myext_domain_model_recipe_' ~ route['recipeId']"],
                $this->createOperation([]),
                ['recipeId' => '42', '_route' => 'recipes_favorite'],
                Request::create('https://example.com/_api/recipes/42/favorite', 'POST')
            )
        );
    }

    /**
     * `object` is not part of the standard tag-expression variable set - `OperationResponseCache`
     * adds it explicitly, via `$additionalVariables`, for `cacheInvalidation.tagExpressions` (the
     * written entity, or `null` on a `DELETE`, see `flushInvalidationTagsAfterSuccessfulWrite()`).
     * This exercises the plumbing directly: a `null` `object` is a valid value, not an evaluation
     * failure, and the null-safe idiom documented for `cacheInvalidation` resolves it to no tag.
     */
    #[Test]
    public function evaluateFlushTagExpressionsResolvesNullObjectViaNullSafeIdiom(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $this->cache->method('isValidTag')->willReturn(true);
        $service = new ResponseCacheService($this->cache, new Context());

        self::assertSame(
            [],
            $service->evaluateFlushTagExpressions(
                ["object != null ? 'tx_myext_domain_model_recipe_' ~ object.getUid() : ''"],
                $this->createOperation([]),
                [],
                Request::create('https://example.com/_api/recipes/42', 'DELETE'),
                ['object' => null]
            )
        );
    }

    /**
     * Fail-soft, unlike the store side above: a failing expression is skipped (with a warning),
     * not fatal to the others - the second, valid expression still contributes its tag.
     */
    #[Test]
    public function evaluateFlushTagExpressionsSkipsFailingExpressionAndLogsWarningButKeepsOthers(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $this->cache->method('isValidTag')->willReturn(true);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        $service = new ResponseCacheService($this->cache, new Context());
        $service->setLogger($logger);

        self::assertSame(
            ['fe_user_1'],
            $service->evaluateFlushTagExpressions(
                ['thisFunctionDoesNotExist()', "'fe_user_1'"],
                $this->createOperation([]),
                [],
                Request::create('https://example.com/_api/books', 'GET')
            )
        );
    }

    #[Test]
    public function evaluateFlushTagExpressionsSkipsInvalidTagAndLogsWarning(): void
    {
        $this->registerPlainSymfonyExpressionResolver();
        $this->cache->method('isValidTag')->willReturn(false);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        $service = new ResponseCacheService($this->cache, new Context());
        $service->setLogger($logger);

        self::assertSame(
            [],
            $service->evaluateFlushTagExpressions(
                ["'invalid tag with spaces'"],
                $this->createOperation([]),
                [],
                Request::create('https://example.com/_api/books', 'GET')
            )
        );
    }

    /**
     * Registers a `Resolver` double backed by a plain Symfony `ExpressionLanguage` (without
     * TYPO3's provider loading, which needs a booted TYPO3 instance) so the conditions are
     * evaluated by the real expression engine against the real variable set built by the
     * service, plus `$providerVariables` standing in for whatever an expression-language
     * provider (e.g. `T3apiCoreProvider`'s `context`) would otherwise contribute - merged the
     * same way the real `Resolver` merges provider variables under per-call ones.
     *
     * @param array<string, mixed> $providerVariables
     */
    private function registerPlainSymfonyExpressionResolver(array $providerVariables = []): void
    {
        GeneralUtility::addInstance(Resolver::class, new class ($providerVariables) extends Resolver {
            private readonly ExpressionLanguage $symfonyExpressionLanguage;

            public function __construct(private readonly array $providerVariables)
            {
                $this->symfonyExpressionLanguage = new ExpressionLanguage();
            }

            public function evaluate(string $expression, array $contextVariables = []): mixed
            {
                return $this->symfonyExpressionLanguage->evaluate(
                    $expression,
                    array_replace($this->providerVariables, $contextVariables)
                );
            }
        });
    }

    private function createSecuredTitleFilter(): ApiFilter
    {
        return new ApiFilter(
            \SourceBroker\T3api\Filter\SearchFilter::class,
            'title',
            ['name' => 'partial', 'condition' => 'is_granted("ROLE_ADMIN")'],
            []
        );
    }

    /**
     * `$cacheAttributes` mirrors an operation's `attributes.cache` block. `null` means caching is
     * disabled entirely (no block declared); a given array is always enabled - same as a non-empty
     * `cache` block without an explicit `enabled` key.
     *
     * @param ApiFilter[]|null $filters
     */
    private function createOperation(?array $cacheAttributes, ?array $filters = null): CollectionOperation
    {
        $responseCacheSettings = $cacheAttributes === null
            ? ResponseCacheSettings::create()
            : ResponseCacheSettings::create(['enabled' => true] + $cacheAttributes);

        $pagination = Pagination::create(PaginationTest::DEFAULT_API_RESOURCE_PAGINATION_ATTRIBUTES);

        $operation = $this->createMock(CollectionOperation::class);
        $operation->method('getResponseCacheSettings')->willReturn($responseCacheSettings);
        $operation->method('getPagination')->willReturn($pagination);
        $operation->method('getFilters')->willReturn($filters ?? [
            new ApiFilter(\SourceBroker\T3api\Filter\SearchFilter::class, 'title', 'partial', []),
        ]);

        return $operation;
    }
}
