<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Domain\Model\CacheInvalidationSettings;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Model\ResponseCacheSettings;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Response\AbstractCollectionResponse;
use SourceBroker\T3api\Security\OperationAccessChecker;
use SourceBroker\T3api\Service\CacheTagCollector;
use SourceBroker\T3api\Service\OperationResponseCache;
use SourceBroker\T3api\Service\ResponseCacheDebugHeaders;
use SourceBroker\T3api\Service\ResponseCacheService;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\PlainBook;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class OperationResponseCacheTest extends UnitTestCase
{
    /**
     * `securedCollectionOperationDeniedThrowsAndNeverTouchesCache()` lets the real, unmocked
     * `LocalizationUtility::translate()` attempt to run (see `AbstractException::translate()`'s
     * fail-soft fallback) - without a full TYPO3 boot it fails, but not before registering a real
     * `Locales` singleton via `GeneralUtility::makeInstance()`, which would otherwise leak into
     * later tests.
     */
    protected bool $resetSingletonInstances = true;

    private MockObject&ResponseCacheService $responseCacheService;

    private CacheTagCollector $cacheTagCollector;

    private MockObject&OperationAccessChecker $operationAccessChecker;

    private MockObject&DataMapper $dataMapper;

    private MockObject&ResponseCacheDebugHeaders $responseCacheDebugHeaders;

    protected function setUp(): void
    {
        parent::setUp();
        $this->responseCacheService = $this->createMock(ResponseCacheService::class);
        $this->cacheTagCollector = new CacheTagCollector();
        $this->operationAccessChecker = $this->createMock(OperationAccessChecker::class);
        $this->dataMapper = $this->createMock(DataMapper::class);
        $this->responseCacheDebugHeaders = $this->createMock(ResponseCacheDebugHeaders::class);
    }

    #[Test]
    public function cacheHitReturnsStoredOutputWithoutProcessing(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturnMap([['entry-id', '{"cached":true}']]);

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled));

        self::assertSame('{"cached":true}', $output);
        self::assertFalse($processorCalled);
    }

    /**
     * `resolve()` decorates the response with `ResponseCacheDebugHeaders::hit()` before returning
     * the cached output - `OperationResponseCache` itself never touches headers or the
     * `Environment` development gate directly, see `ResponseCacheDebugHeadersTest` for that.
     */
    #[Test]
    public function cacheHitDecoratesResponseViaDebugHeadersCollaborator(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturnMap([['entry-id', '{"cached":true}']]);
        $this->responseCacheDebugHeaders->expects(self::once())
            ->method('hit')
            ->with(self::anything(), 'entry-id');

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled));

        self::assertSame('{"cached":true}', $output);
    }

    #[Test]
    public function cacheMissProcessesAndStoresOutput(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', self::anything(), self::anything());

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled));

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    /**
     * `storeWithTags()` reports the exact tag set it just passed to
     * `ResponseCacheService::store()` to `ResponseCacheDebugHeaders::stored()` - here the default
     * operation contributes no content, literal or expression tags at all, so the reported set is
     * empty, same as what `store()` itself receives.
     */
    #[Test]
    public function cacheMissDecoratesResponseWithStoredTagsViaDebugHeadersCollaborator(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheDebugHeaders->expects(self::once())
            ->method('stored')
            ->with(self::anything(), 'entry-id', []);

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled));

        self::assertSame('{"fresh":true}', $output);
    }

    #[Test]
    public function nonSuccessfulResponseIsNotStored(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('store');

        $processorCalled = false;
        $response = new Response();
        $output = $this->resolve(
            $this->processor('{"error":true}', $processorCalled, $response, 500),
            response: $response
        );

        self::assertSame('{"error":true}', $output);
    }

    #[Test]
    public function nonCacheableRequestSkipsCacheEntirely(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('get');
        $this->responseCacheService->expects(self::never())->method('store');

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled));

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    #[Test]
    public function disallowedReadSkipsCacheLookupButStillStores(): void
    {
        $this->allowConditions(read: false);
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->expects(self::never())->method('get');
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', self::anything(), self::anything());

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled));

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    #[Test]
    public function disallowedWriteProcessesButDoesNotStore(): void
    {
        $this->allowConditions(write: false);
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('store');

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled));

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    #[Test]
    public function cacheMissSeedsResourceTableAndSingleScopeTagsForItemOperation(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->dataMapper->method('getDataMap')->willReturnMap([
            [PlainBook::class, new DataMap(PlainBook::class, 'tx_test_domain_model_plainbook')],
        ]);
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with(
                'entry-id',
                '{"fresh":true}',
                ['tx_test_domain_model_plainbook', 'tx_test_domain_model_plainbook--single'],
                self::anything()
            );

        $processorCalled = false;
        $output = $this->resolve(
            $this->processor('{"fresh":true}', $processorCalled),
            $this->createOperation(PlainBook::class)
        );

        self::assertSame('{"fresh":true}', $output);
    }

    #[Test]
    public function cacheMissSeedsResourceTableAndCollectionScopeTagsForCollectionOperation(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->dataMapper->method('getDataMap')->willReturnMap([
            [PlainBook::class, new DataMap(PlainBook::class, 'tx_test_domain_model_plainbook')],
        ]);
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with(
                'entry-id',
                '{"fresh":true}',
                ['tx_test_domain_model_plainbook', 'tx_test_domain_model_plainbook--collection'],
                self::anything()
            );

        $processorCalled = false;
        $output = $this->resolve(
            $this->processor('{"fresh":true}', $processorCalled),
            $this->createOperation(PlainBook::class, isCollectionOperation: true)
        );

        self::assertSame('{"fresh":true}', $output);
    }

    #[Test]
    public function cacheMissSkipsTableTagSeedingForNonEntityResource(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->dataMapper->expects(self::never())->method('getDataMap');
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', [], self::anything());

        $processorCalled = false;
        $output = $this->resolve(
            $this->processor('{"fresh":true}', $processorCalled),
            $this->createOperation(\stdClass::class)
        );

        self::assertSame('{"fresh":true}', $output);
    }

    #[Test]
    public function cacheMissStoresConfiguredLiteralTags(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->method('isValidTag')->willReturn(true);
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', ['pages', 'tx_test_domain_model_recipe'], self::anything());

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'tags' => ['pages', 'tx_test_domain_model_recipe'],
        ]));

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation);

        self::assertSame('{"fresh":true}', $output);
    }

    /**
     * `tags` are pure literals with no route substitution - a route-driven tag belongs in
     * `tagExpressions` instead, via the `route` variable (see `ResponseCacheServiceTest` for
     * coverage of the expression evaluating that variable). This only asserts the wiring: the
     * exact `$route` given to `resolve()` reaches `evaluateStoreTagExpressions()` unchanged.
     */
    #[Test]
    public function routeIsForwardedToStoreTagExpressionEvaluation(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->expects(self::once())
            ->method('evaluateStoreTagExpressions')
            ->with(self::anything(), self::anything(), ['recipeId' => '42'], self::anything())
            ->willReturn([]);

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'tagExpressions' => ["'tx_test_domain_model_recipe_' ~ route['recipeId']"],
        ]));

        $processorCalled = false;
        $output = $this->resolve(
            $this->processor('{"fresh":true}', $processorCalled),
            $operation,
            route: ['recipeId' => '42']
        );

        self::assertSame('{"fresh":true}', $output);
    }

    #[Test]
    public function cacheMissStoresExpressionProducedTagAlongsideStaticTags(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->method('isValidTag')->willReturn(true);
        $this->responseCacheService->method('evaluateStoreTagExpressions')->willReturn(['fe_user_1']);
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', ['pages', 'fe_user_1'], self::anything());

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'tags' => ['pages'],
            'tagExpressions' => ["user.isLoggedIn() ? 'fe_user_' ~ user.getUid() : ''"],
        ]));

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation);

        self::assertSame('{"fresh":true}', $output);
    }

    /**
     * An empty string is the documented idiom for "no tag from this expression" (e.g. an anonymous
     * visitor with `user.isLoggedIn() ? ... : ''`) - it must not be treated as a failure, unlike
     * `cacheMissDoesNotStoreWhenTagExpressionEvaluationFails()` below.
     */
    #[Test]
    public function cacheMissStoresEntryWhenTagExpressionsResolveToNoTags(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->method('evaluateStoreTagExpressions')->willReturn([]);
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', [], self::anything());

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'tagExpressions' => ["user.isLoggedIn() ? 'fe_user_' ~ user.getUid() : ''"],
        ]));

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation);

        self::assertSame('{"fresh":true}', $output);
    }

    /**
     * `null` from `evaluateStoreTagExpressions()` signals a failed evaluation (or an invalid
     * resulting tag) - the entry's invalidation contract can no longer be guaranteed, so it must
     * not be stored at all (fail-closed). The response itself is still returned to the caller -
     * only the store is skipped, the request does not fail because of it.
     */
    #[Test]
    public function cacheMissDoesNotStoreWhenTagExpressionEvaluationFails(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->method('evaluateStoreTagExpressions')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('store');

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'tagExpressions' => ['thisFunctionDoesNotExist()'],
        ]));

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation);

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    /**
     * An item GET's `$result` is the single result entity - `memberTagExpressions` evaluates
     * exactly once, against that entity as `object`.
     */
    #[Test]
    public function cacheMissEvaluatesMemberTagExpressionsOnceForItemGetResultEntity(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);

        $entity = $this->createMock(AbstractDomainObject::class);
        $this->responseCacheService->expects(self::once())
            ->method('evaluateStoreTagExpressions')
            ->with(["'author_' ~ object.getAuthor().getUid()"], self::anything(), self::anything(), self::anything(), ['object' => $entity])
            ->willReturn(['author_1']);
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', ['author_1'], self::anything());

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'memberTagExpressions' => ["'author_' ~ object.getAuthor().getUid()"],
        ]));

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation, result: $entity);

        self::assertSame('{"fresh":true}', $output);
    }

    /**
     * A collection GET's `$result` is an `AbstractCollectionResponse` - `memberTagExpressions`
     * evaluates once per member, and the resulting tags are unioned and deduplicated across all of
     * them, on top of the automatic content tags.
     */
    #[Test]
    public function cacheMissEvaluatesMemberTagExpressionsOncePerCollectionMemberAndUnionsTags(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);

        $firstMember = $this->createMock(AbstractDomainObject::class);
        $secondMember = $this->createMock(AbstractDomainObject::class);
        $this->responseCacheService->expects(self::exactly(2))
            ->method('evaluateStoreTagExpressions')
            ->willReturnCallback(function (array $expressions, $operation, $route, $request, array $additionalVariables) use ($firstMember, $secondMember) {
                return match ($additionalVariables['object']) {
                    $firstMember => ['author_1'],
                    $secondMember => ['author_2'],
                    default => self::fail('Unexpected member evaluated: ' . get_debug_type($additionalVariables['object'])),
                };
            });
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', ['author_1', 'author_2'], self::anything());

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'memberTagExpressions' => ["'author_' ~ object.getAuthor().getUid()"],
        ]));

        $processorCalled = false;
        $output = $this->resolve(
            $this->processor('{"fresh":true}', $processorCalled),
            $operation,
            result: $this->createCollectionResponseStub([$firstMember, $secondMember])
        );

        self::assertSame('{"fresh":true}', $output);
    }

    /**
     * An empty collection has no member to evaluate `memberTagExpressions` against - zero
     * evaluations, zero member tags, and the store still proceeds (matching the resource-level
     * `tagExpressions`/table-seed tags, which are unaffected by this setting).
     */
    #[Test]
    public function cacheMissSkipsMemberTagExpressionsEvaluationForEmptyCollection(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('evaluateStoreTagExpressions');
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', [], self::anything());

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'memberTagExpressions' => ["'author_' ~ object.getAuthor().getUid()"],
        ]));

        $processorCalled = false;
        $output = $this->resolve(
            $this->processor('{"fresh":true}', $processorCalled),
            $operation,
            result: $this->createCollectionResponseStub([])
        );

        self::assertSame('{"fresh":true}', $output);
    }

    /**
     * Mirrors `cacheMissDoesNotStoreWhenTagExpressionEvaluationFails()`: a single member's
     * `memberTagExpressions` evaluation failure means the entry's invalidation contract can no
     * longer be guaranteed for the whole response, so the entry is not stored at all - not even a
     * partial tag set from the members that did evaluate successfully.
     */
    #[Test]
    public function cacheMissDoesNotStoreWhenAMemberTagExpressionEvaluationFails(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->method('evaluateStoreTagExpressions')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('store');

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'memberTagExpressions' => ['thisFunctionDoesNotExist()'],
        ]));

        $processorCalled = false;
        $output = $this->resolve(
            $this->processor('{"fresh":true}', $processorCalled),
            $operation,
            result: $this->createMock(AbstractDomainObject::class)
        );

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    /**
     * A collection member that is not an `AbstractDomainObject` is skipped with a logged warning
     * rather than evaluated against - it never reaches `evaluateStoreTagExpressions()` at all, and
     * never blocks the other, valid member from being evaluated.
     */
    #[Test]
    public function cacheMissSkipsNonDomainObjectCollectionMemberWithLoggedWarning(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);

        $validMember = $this->createMock(AbstractDomainObject::class);
        $this->responseCacheService->expects(self::once())
            ->method('evaluateStoreTagExpressions')
            ->with(self::anything(), self::anything(), self::anything(), self::anything(), ['object' => $validMember])
            ->willReturn(['author_1']);
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', ['author_1'], self::anything());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'memberTagExpressions' => ["'author_' ~ object.getAuthor().getUid()"],
        ]));

        $operationResponseCache = $this->createOperationResponseCache();
        $operationResponseCache->setLogger($logger);

        $processorCalled = false;
        $response = new Response();
        $result = $this->createCollectionResponseStub(['not-a-domain-object', $validMember]);
        $output = $operationResponseCache->resolve(
            $operation,
            [],
            Request::create('https://example.com/_api/books', 'GET'),
            $this->processor('{"fresh":true}', $processorCalled),
            $response,
            $result
        );

        self::assertSame('{"fresh":true}', $output);
    }

    #[Test]
    public function invalidationTagsFlushedAfterSuccessfulNonGetOperation(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->method('isValidTag')->willReturn(true);
        $this->responseCacheService->expects(self::once())
            ->method('flushByTags')
            ->with(['tx_test_domain_model_recipe']);
        $this->responseCacheService->expects(self::never())->method('store');

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create([
                'tags' => ['tx_test_domain_model_recipe'],
            ]),
            isMethodGet: false
        );

        $processorCalled = false;
        $response = new Response();
        $output = $this->resolve(
            $this->processor('{"toggled":true}', $processorCalled, $response),
            $operation,
            $response
        );

        self::assertSame('{"toggled":true}', $output);
        self::assertTrue($processorCalled);
    }

    /**
     * `flushInvalidationTagsAfterSuccessfulWrite()` reports the exact tag set it just passed to
     * `ResponseCacheService::flushByTags()` to `ResponseCacheDebugHeaders::flushed()`.
     */
    #[Test]
    public function invalidationFlushDecoratesResponseWithFlushedTagsViaDebugHeadersCollaborator(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->method('isValidTag')->willReturn(true);
        $this->responseCacheDebugHeaders->expects(self::once())
            ->method('flushed')
            ->with(self::anything(), ['tx_test_domain_model_recipe']);

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create([
                'tags' => ['tx_test_domain_model_recipe'],
            ]),
            isMethodGet: false
        );

        $processorCalled = false;
        $response = new Response();
        $output = $this->resolve(
            $this->processor('{"toggled":true}', $processorCalled, $response),
            $operation,
            $response
        );

        self::assertSame('{"toggled":true}', $output);
    }

    /**
     * `tags` are pure literals with no route substitution - a route-driven flush tag belongs in
     * `tagExpressions` instead, via the `route` variable (see `ResponseCacheServiceTest` for
     * coverage of the expression evaluating that variable). This only asserts the wiring: the
     * exact `$route` given to `resolve()` reaches `evaluateFlushTagExpressions()` unchanged.
     */
    #[Test]
    public function routeIsForwardedToFlushTagExpressionEvaluation(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->expects(self::once())
            ->method('evaluateFlushTagExpressions')
            ->with(self::anything(), self::anything(), ['recipeId' => '7'], self::anything())
            ->willReturn([]);

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create([
                'tagExpressions' => ["'tx_test_domain_model_recipe_' ~ route['recipeId']"],
            ]),
            isMethodGet: false
        );

        $processorCalled = false;
        $response = new Response();
        $output = $this->resolve(
            $this->processor('{"toggled":true}', $processorCalled, $response),
            $operation,
            $response,
            ['recipeId' => '7']
        );

        self::assertSame('{"toggled":true}', $output);
    }

    #[Test]
    public function invalidationFlushMergesStaticAndExpressionTags(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->method('isValidTag')->willReturn(true);
        $this->responseCacheService->method('evaluateFlushTagExpressions')->willReturn(['fe_user_1']);
        $this->responseCacheService->expects(self::once())
            ->method('flushByTags')
            ->with(['tx_test_domain_model_recipe', 'fe_user_1']);

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create([
                'tags' => ['tx_test_domain_model_recipe'],
                'tagExpressions' => ["'fe_user_' ~ user.getUid()"],
            ]),
            isMethodGet: false
        );

        $processorCalled = false;
        $response = new Response();
        $output = $this->resolve(
            $this->processor('{"toggled":true}', $processorCalled, $response),
            $operation,
            $response
        );

        self::assertSame('{"toggled":true}', $output);
    }

    /**
     * `cacheInvalidation.tagExpressions` sees `object`, bound to the persisted entity the write
     * operation returned - unlike `cache.tagExpressions`, which never has it (see
     * `ResponseCacheServiceTest` for the plumbing this exercises the wiring of).
     */
    #[Test]
    public function invalidationFlushExposesWrittenEntityAsObjectVariable(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);

        $writtenEntity = $this->createMock(AbstractDomainObject::class);
        $this->responseCacheService->expects(self::once())
            ->method('evaluateFlushTagExpressions')
            ->with(self::anything(), self::anything(), self::anything(), self::anything(), ['object' => $writtenEntity])
            ->willReturn([]);

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create([
                'tagExpressions' => ["object != null ? 'tx_test_domain_model_recipe_' ~ object.getUid() : ''"],
            ]),
            isMethodGet: false
        );

        $processorCalled = false;
        $response = new Response();
        $output = $this->resolve(
            $this->processor('{"toggled":true}', $processorCalled, $response),
            $operation,
            $response,
            result: $writtenEntity
        );

        self::assertSame('{"toggled":true}', $output);
    }

    /**
     * A `DELETE` handler (or any custom write handler returning nothing) leaves `$result` `null` -
     * `cacheInvalidation.tagExpressions` still runs, with `object` bound to `null` rather than being
     * skipped; the documented null-safe idiom (`object != null ? ... : ''`) is what a `tagExpressions`
     * entry uses to handle this without erroring.
     */
    #[Test]
    public function invalidationFlushExposesNullObjectVariableWhenResultIsNull(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->expects(self::once())
            ->method('evaluateFlushTagExpressions')
            ->with(self::anything(), self::anything(), self::anything(), self::anything(), ['object' => null])
            ->willReturn([]);

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create([
                'tagExpressions' => ["object != null ? 'tx_test_domain_model_recipe_' ~ object.getUid() : ''"],
            ]),
            isMethodGet: false
        );

        $processorCalled = false;
        $response = new Response();
        $output = $this->resolve(
            $this->processor('{"deleted":true}', $processorCalled, $response),
            $operation,
            $response
        );

        self::assertSame('{"deleted":true}', $output);
    }

    /**
     * Fail-soft, unlike the store side above: `ResponseCacheService` already filters a failing (or
     * empty-result) expression out of what it returns, logging its own warning - the write already
     * succeeded, so the flush proceeds with whatever tags remain (here, only the static one) rather
     * than being skipped entirely.
     */
    #[Test]
    public function invalidationFlushStillOccursWithStaticTagsWhenExpressionTagsAreEmpty(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->method('isValidTag')->willReturn(true);
        $this->responseCacheService->method('evaluateFlushTagExpressions')->willReturn([]);
        $this->responseCacheService->expects(self::once())
            ->method('flushByTags')
            ->with(['tx_test_domain_model_recipe']);

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create([
                'tags' => ['tx_test_domain_model_recipe'],
                'tagExpressions' => ['thisFunctionDoesNotExist()'],
            ]),
            isMethodGet: false
        );

        $processorCalled = false;
        $response = new Response();
        $output = $this->resolve(
            $this->processor('{"toggled":true}', $processorCalled, $response),
            $operation,
            $response
        );

        self::assertSame('{"toggled":true}', $output);
    }

    #[Test]
    public function invalidationTagsNotFlushedWhenProcessorThrows(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('flushByTags');

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create(['tags' => ['some_tag']]),
            isMethodGet: false
        );

        $this->expectException(\RuntimeException::class);

        $this->resolve(
            static function (): string {
                throw new \RuntimeException('write failed');
            },
            $operation
        );
    }

    /**
     * A resource-level `cacheInvalidation` block cascades onto every operation, including a `GET`
     * one - but a `GET` operation never writes anything, so it must never act on inherited
     * invalidation tags either, unlike a non-GET operation (see
     * `invalidationTagsFlushedAfterSuccessfulNonGetOperation` above).
     */
    #[Test]
    public function getOperationWithInheritedInvalidationTagsNeverFlushes(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('flushByTags');
        $this->responseCacheService->expects(self::never())->method('store');

        $operation = $this->createOperation(
            cacheInvalidationSettings: CacheInvalidationSettings::create([
                'tags' => ['tx_test_domain_model_recipe'],
            ]),
            isMethodGet: true
        );

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation);

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    #[Test]
    public function invalidTagIsSkippedAndLogged(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->method('isValidTag')->willReturnCallback(
            static fn(string $tag): bool => preg_match('/^[a-zA-Z0-9_%\\-&]{1,250}$/', $tag) === 1
        );
        $this->responseCacheService->expects(self::once())
            ->method('store')
            ->with('entry-id', '{"fresh":true}', [], self::anything());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('warning');

        $operation = $this->createOperation(responseCacheSettings: ResponseCacheSettings::create([
            'enabled' => true,
            'tags' => ['invalid tag with spaces', 'tag/with/slash'],
        ]));

        $operationResponseCache = $this->createOperationResponseCache();
        $operationResponseCache->setLogger($logger);

        $processorCalled = false;
        $response = new Response();
        $output = $operationResponseCache->resolve(
            $operation,
            [],
            Request::create('https://example.com/_api/books', 'GET'),
            $this->processor('{"fresh":true}', $processorCalled),
            $response
        );

        self::assertSame('{"fresh":true}', $output);
    }

    #[Test]
    public function emptyCacheSettingsResultInNoFlushCalls(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->expects(self::never())->method('flushByTags');

        $processorCalled = false;
        $output = $this->resolve($this->processor('{"fresh":true}', $processorCalled));

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    #[Test]
    public function securedCollectionOperationServesHitAndChecksAccessOnEveryResolve(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn('{"cached":true}');
        $this->operationAccessChecker->expects(self::exactly(2))->method('isGranted')->willReturn(true);

        $operation = $this->createSecuredCollectionOperation();
        $processorCalled = false;

        self::assertSame(
            '{"cached":true}',
            $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation)
        );
        self::assertSame(
            '{"cached":true}',
            $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation)
        );
        self::assertFalse($processorCalled);
    }

    /**
     * `OperationNotAllowedException` translates its title/description via TYPO3's `LanguageService`
     * - `AbstractException::translate()` falls back to the raw key when that fails, so this needs
     * no TYPO3 translation machinery faked at all, and stays correct across the whole supported
     * TYPO3/PHPUnit version range.
     */
    #[Test]
    public function securedCollectionOperationDeniedThrowsAndNeverTouchesCache(): void
    {
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->expects(self::never())->method('get');
        $this->responseCacheService->expects(self::never())->method('store');
        $this->operationAccessChecker->method('isGranted')->willReturn(false);

        $operation = $this->createSecuredCollectionOperation();
        $processorCalled = false;

        $this->expectException(OperationNotAllowedException::class);

        $this->resolve($this->processor('{"fresh":true}', $processorCalled), $operation);
    }

    #[Test]
    public function securedItemOperationRetriesGrantCheckWithLoadedEntityWhenExpressionNeedsObject(): void
    {
        $this->allowConditions();
        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->method('get')->willReturn(null);
        $this->responseCacheService->expects(self::once())->method('store');

        $entity = $this->createMock(AbstractDomainObject::class);
        $operation = $this->createSecuredItemOperation();

        $this->operationAccessChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->willReturnCallback(function (OperationInterface $calledOperation, array $variables = []) use ($entity) {
                if ($variables === []) {
                    throw new \RuntimeException('Variable "object" is not valid.');
                }

                self::assertSame($entity, $variables['object'] ?? null);

                return true;
            });

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        $operationResponseCache = $this->createOperationResponseCacheWithStubbedEntityLoad($entity);
        $operationResponseCache->setLogger($logger);

        $processorCalled = false;
        $response = new Response();
        $output = $operationResponseCache->resolve(
            $operation,
            ['id' => '5'],
            Request::create('https://example.com/_api/books/5', 'GET'),
            $this->processor('{"fresh":true}', $processorCalled),
            $response
        );

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    #[Test]
    public function securedItemOperationDelegatesToProcessorWhenEntityCannotBeLoaded(): void
    {
        $operation = $this->createSecuredItemOperation();

        $this->responseCacheService->method('buildEntryIdentifier')->willReturn('entry-id');
        $this->responseCacheService->expects(self::never())->method('get');
        $this->responseCacheService->expects(self::never())->method('store');
        $this->operationAccessChecker->method('isGranted')->willThrowException(
            new \RuntimeException('Variable "object" is not valid.')
        );
        $this->responseCacheDebugHeaders->expects(self::once())->method('lookupOutcome')->with(self::anything(), 'miss');

        $operationResponseCache = $this->createOperationResponseCacheWithStubbedEntityLoad(null);

        $processorCalled = false;
        $response = new Response();
        $output = $operationResponseCache->resolve(
            $operation,
            ['id' => '404'],
            Request::create('https://example.com/_api/books/404', 'GET'),
            $this->processor('{"fresh":true}', $processorCalled, $response),
            $response
        );

        self::assertSame('{"fresh":true}', $output);
        self::assertTrue($processorCalled);
    }

    private function allowConditions(bool $read = true, bool $write = true): void
    {
        $this->responseCacheService->method('isReadAllowed')->willReturn($read);
        $this->responseCacheService->method('isWriteAllowed')->willReturn($write);
    }

    /**
     * Builds a `resolve()` processor closure that records whether it ran and,
     * when `$forcedStatusCode` is given, mutates the by-reference `$response`
     * the same way a real operation handler would.
     */
    private function processor(
        string $cannedOutput,
        bool &$called,
        ?ResponseInterface &$response = null,
        ?int $forcedStatusCode = null
    ): callable {
        return function () use ($cannedOutput, &$called, &$response, $forcedStatusCode): string {
            $called = true;
            if ($forcedStatusCode !== null && $response !== null) {
                $response = $response->withStatus($forcedStatusCode);
            }

            return $cannedOutput;
        };
    }

    /**
     * `$result` stands in for what `AbstractDispatcher::processOperation()`'s closure would have
     * written by the time `OperationResponseCache::resolve()` reaches the store/flush step - the
     * fixture processors above never touch it themselves, so tests exercising `object` simply give
     * the entity they want to see up front.
     */
    private function resolve(
        callable $processor,
        ?OperationInterface $operation = null,
        ?ResponseInterface &$response = null,
        array $route = [],
        mixed $result = null
    ): string {
        $response ??= new Response();

        return $this->createOperationResponseCache()->resolve(
            $operation ?? $this->createOperation(),
            $route,
            Request::create('https://example.com/_api/books', 'GET'),
            $processor,
            $response,
            $result
        );
    }

    private function createOperationResponseCache(): OperationResponseCache
    {
        return new OperationResponseCache(
            $this->responseCacheService,
            $this->cacheTagCollector,
            $this->operationAccessChecker,
            $this->dataMapper,
            $this->responseCacheDebugHeaders
        );
    }

    /**
     * A minimal `AbstractCollectionResponse` double whose `getMembers()` returns the given array
     * directly, bypassing pagination/query execution entirely - unit tests exercising
     * `memberTagExpressions` only need control over the member set, not a working Extbase query.
     *
     * @param mixed[] $members
     */
    private function createCollectionResponseStub(array $members): AbstractCollectionResponse
    {
        return new class ($this->createMock(CollectionOperation::class), Request::create('https://example.com/_api/books', 'GET'), $this->createMock(QueryInterface::class), $members) extends AbstractCollectionResponse {
            /**
             * @param mixed[] $stubbedMembers
             */
            public function __construct(
                CollectionOperation $operation,
                Request $request,
                QueryInterface $query,
                private readonly array $stubbedMembers
            ) {
                parent::__construct($operation, $request, $query);
            }

            public static function getOpenApiSchema(string $membersReference): Schema
            {
                throw new \LogicException('Not used by this test double.');
            }

            public function getMembers(): array
            {
                return $this->stubbedMembers;
            }
        };
    }

    /**
     * Builds an `OperationResponseCache` with `loadItemOperationObject()` stubbed,
     * since exercising the real `CommonRepository::getInstanceForOperation()` static
     * factory needs a booted TYPO3 persistence layer unavailable to a unit test.
     */
    private function createOperationResponseCacheWithStubbedEntityLoad(
        ?AbstractDomainObject $entity
    ): MockObject&OperationResponseCache {
        $operationResponseCache = $this->getMockBuilder(OperationResponseCache::class)
            ->setConstructorArgs([
                $this->responseCacheService,
                $this->cacheTagCollector,
                $this->operationAccessChecker,
                $this->dataMapper,
                $this->responseCacheDebugHeaders,
            ])
            ->onlyMethods(['loadItemOperationObject'])
            ->getMock();
        $operationResponseCache->method('loadItemOperationObject')->willReturn($entity);

        return $operationResponseCache;
    }

    /**
     * Builds an `OperationInterface` mock exposing real cache settings, since
     * `resolve()` reads the lifetime from it on the store path (the mocked
     * `responseCacheService` in this test does not enforce the real
     * disabled-cache-settings invariant). `getSecurity()` defaults to an empty
     * string (the mock default for an unconfigured `string` return type), so
     * the security gate never engages for this operation. `isMethodGet()` defaults
     * to `true` since most callers exercise the cacheable `GET` path; pass `false`
     * to build a write operation for the invalidation-flush tests.
     *
     * `getApiResource()->getEntity()` is left unstubbed by default, which the mock
     * resolves to `''` - `is_subclass_of('', AbstractDomainObject::class)` is `false`,
     * so table tag seeding is a no-op unless `$entityClass` names an actual resource.
     * `$isCollectionOperation` builds a `CollectionOperation` mock instead of a plain
     * `OperationInterface` one - needed for `seedResourceTableTag()`'s
     * `$operation instanceof CollectionOperation` scope-tag check, since the default
     * `OperationInterface` mock is never an instance of it.
     */
    private function createOperation(
        string $entityClass = '',
        ?ResponseCacheSettings $responseCacheSettings = null,
        ?CacheInvalidationSettings $cacheInvalidationSettings = null,
        bool $isMethodGet = true,
        bool $isCollectionOperation = false
    ): OperationInterface {
        $operation = $isCollectionOperation
            ? $this->createMock(CollectionOperation::class)
            : $this->createMock(OperationInterface::class);
        $operation->method('isMethodGet')->willReturn($isMethodGet);
        $operation->method('getResponseCacheSettings')->willReturn(
            $responseCacheSettings ?? ResponseCacheSettings::create(['enabled' => true])
        );
        $operation->method('getCacheInvalidationSettings')->willReturn(
            $cacheInvalidationSettings ?? CacheInvalidationSettings::create()
        );

        if ($entityClass !== '') {
            $apiResource = $this->createMock(ApiResource::class);
            $apiResource->method('getEntity')->willReturn($entityClass);
            $operation->method('getApiResource')->willReturn($apiResource);
        }

        return $operation;
    }

    private function createSecuredCollectionOperation(string $security = '1 == 1'): CollectionOperation&MockObject
    {
        $operation = $this->createMock(CollectionOperation::class);
        $operation->method('getSecurity')->willReturn($security);
        $operation->method('getPath')->willReturn('/books');
        $operation->method('isMethodGet')->willReturn(true);
        $operation->method('getResponseCacheSettings')->willReturn(
            ResponseCacheSettings::create(['enabled' => true])
        );

        return $operation;
    }

    private function createSecuredItemOperation(string $security = 'object.isPublic'): ItemOperation&MockObject
    {
        $apiResource = $this->createMock(ApiResource::class);
        $apiResource->method('getEntity')->willReturn('Vendor\\SiteExtension\\Domain\\Model\\Book');

        $operation = $this->createMock(ItemOperation::class);
        $operation->method('getSecurity')->willReturn($security);
        $operation->method('getKey')->willReturn('get');
        $operation->method('getPath')->willReturn('/books/{id}');
        $operation->method('getApiResource')->willReturn($apiResource);
        $operation->method('isMethodGet')->willReturn(true);
        $operation->method('getResponseCacheSettings')->willReturn(
            ResponseCacheSettings::create(['enabled' => true])
        );

        return $operation;
    }
}
