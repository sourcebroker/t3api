<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Event\RecordUpdatedEvent;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Full-cycle functional test for the response cache dispatcher integration:
 * MISS (fresh output) -> HIT (stale output served after a direct SQL change) ->
 * invalidation via the real PSR-14 event dispatcher -> MISS again with fresh data.
 *
 * Both scenarios live in this single test class deliberately: `SiteService::getCurrent()`
 * and `RouteService`'s route-enhancer lookup memoize their result in function-local static
 * variables for the lifetime of the PHP process, so every scenario that depends on site/route
 * resolution has to run against the one site configuration written in setUp().
 */
class ResponseCacheDispatcherTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/t3api',
        'typo3conf/ext/t3api/Tests/Functional/Fixtures/Extensions/t3api_response_cache_test',
    ];

    private const TABLE = 'tx_responsecachetest_domain_model_book';

    private const AUTHOR_TABLE = 'tx_responsecachetest_domain_model_author';

    private const SITE_IDENTIFIER = 'functional-test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/books.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/authors.csv');
        $this->writeSiteConfiguration();

        // SiteService::getCurrent() falls back to TYPO3\CMS\Core\Http\ServerRequestFactory::fromGlobals(),
        // which reads the real PHP superglobals (not $GLOBALS['TYPO3_REQUEST']) and refuses to build a
        // request URL on CLI unless $_SERVER carries one - fake it so site resolution succeeds.
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/_api/books';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $site = $this->get(SiteFinder::class)->getSiteByIdentifier(self::SITE_IDENTIFIER);

        // Extbase's ConfigurationManager resolves this request through $GLOBALS['TYPO3_REQUEST'] to build
        // the persistence QuerySettings used by CommonRepository. Marking it FE would route through
        // FrontendConfigurationManager, which hard-requires a `frontend.typoscript` request attribute that
        // is normally populated by the frontend middleware stack we deliberately bypass here. Marking it BE
        // routes through BackendConfigurationManager instead, which is built to gracefully compute a
        // (page-less, in our case) TypoScript setup without a real page tree - this is the same mechanism
        // every Extbase backend module relies on. `applicationType` itself is not read anywhere in t3api's
        // own code, so this only affects Extbase's internal TypoScript bootstrapping, not dispatch behaviour.
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/_api/books', 'GET'))
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getDefaultLanguage())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
    }

    #[Test]
    public function collectionResponseIsCachedAndInvalidatedByRecordUpdatedEvent(): void
    {
        // 1. MISS - fresh output contains the fixture title.
        $firstOutput = $this->dispatchCollectionGet();
        self::assertStringContainsString('First book', $firstOutput);

        // 2. Change the row behind t3api's back - a cache HIT must still serve the stale output.
        $this->updateBookTitle('Changed title');
        self::assertStringContainsString(
            'First book',
            $this->dispatchCollectionGet(),
            'Second request must be served from cache and therefore still show the pre-update title.'
        );

        // 3. Dispatch the invalidation event - next request must be a MISS with fresh data.
        $this->get(EventDispatcherInterface::class)->dispatch(new RecordUpdatedEvent(1, self::TABLE));
        self::assertStringContainsString(
            'Changed title',
            $this->dispatchCollectionGet(),
            'After invalidation the cache must be bypassed and fresh data returned.'
        );
    }

    #[Test]
    public function emptyCollectionResponseIsCachedAndInvalidatedWhenFirstRecordIsCreated(): void
    {
        $this->deleteAllBooks();

        // 1. MISS - fresh output is an empty collection, but the response cache now seeds the
        // resource table tag before serialization, so this entry is invalidated by the table
        // tag below even though serialization itself never touched a single `Book` instance.
        $firstOutput = $this->dispatchCollectionGet();
        self::assertStringNotContainsString('First book', $firstOutput);
        self::assertStringNotContainsString('Second book', $firstOutput);

        // 2. Insert a row behind t3api's back - a cache HIT must still serve the stale, empty output.
        $newBookUid = $this->insertBook('Newly inserted book');
        $secondOutput = $this->dispatchCollectionGet();
        self::assertStringNotContainsString(
            'Newly inserted book',
            $secondOutput,
            'Second request must be served from cache and therefore still show the pre-insert (empty) collection.'
        );

        // 3. Dispatch the invalidation event for the new record - next request must be a MISS with fresh data.
        $this->get(EventDispatcherInterface::class)->dispatch(
            new RecordUpdatedEvent($newBookUid, self::TABLE, changesCollectionMembership: true)
        );
        self::assertStringContainsString(
            'Newly inserted book',
            $this->dispatchCollectionGet(),
            'After invalidation the cache must be bypassed and the newly created record returned.'
        );
    }

    #[Test]
    public function collectionResponseWithDeclaredTagIsInvalidatedByDirectTagFlush(): void
    {
        // 1. MISS - fresh output contains the fixture title, stored under the operation's
        // declared `"tags"={"functional_custom_tag"}` on top of the automatic content tags.
        $firstOutput = $this->dispatchCollectionGet();
        self::assertStringContainsString('First book', $firstOutput);

        // 2. Change the row behind t3api's back - a cache HIT must still serve the stale output.
        $this->updateBookTitle('Changed via declared tag');
        self::assertStringContainsString(
            'First book',
            $this->dispatchCollectionGet(),
            'Second request must be served from cache before the declared tag is flushed.'
        );

        // 3. Flush the declared tag directly via the cache frontend - bypassing t3api's own
        // automatic record-change invalidation entirely - to prove the tag was actually stored
        // on the entry. The next request must be a MISS with fresh data.
        $this->get(CacheManager::class)->getCache('t3api_response')->flushByTag('functional_custom_tag');
        self::assertStringContainsString(
            'Changed via declared tag',
            $this->dispatchCollectionGet(),
            'After flushing the declared custom tag directly, the next request must be a MISS with fresh data.'
        );
    }

    /**
     * `Book`'s collection `GET` declares `"memberTagExpressions"={"'author_' ~ object.getAuthor().getUid()"}`
     * (see the fixture) - evaluated once per collection member (`object` bound to that one `Book`),
     * not once for the whole response like `tagExpressions`. Book 1 embeds Author A (uid 1), book 2
     * embeds Author B (uid 2, never touched here) - flushing `author_1` directly proves the tag was
     * actually derived per member and reaches the stored entry, end to end through the real
     * dispatcher/cache-frontend wiring (unlike `OperationResponseCacheTest`, which only asserts the
     * wiring against a mocked `ResponseCacheService`).
     */
    #[Test]
    public function collectionResponseWithMemberTagExpressionIsInvalidatedByDirectAuthorTagFlush(): void
    {
        // 1. MISS - fresh output, stored under (among others) the `author_1`/`author_2` tags derived
        // from `memberTagExpressions`, one evaluation per collection member.
        $firstOutput = $this->dispatchCollectionGet();
        self::assertStringContainsString('First book', $firstOutput);

        // 2. Change the row behind t3api's back - a cache HIT must still serve the stale output.
        $this->updateBookTitle('Changed via member tag expression');
        self::assertStringContainsString(
            'First book',
            $this->dispatchCollectionGet(),
            'Second request must be served from cache before the member-derived tag is flushed.'
        );

        // 3. Flush the tag derived from book 1's author (Author A, uid 1) directly via the cache
        // frontend - bypassing t3api's own automatic record-change invalidation entirely - to prove
        // `memberTagExpressions` actually tagged the entry per member. The next request must be a
        // MISS with fresh data.
        $this->get(CacheManager::class)->getCache('t3api_response')->flushByTag('author_1');
        self::assertStringContainsString(
            'Changed via member tag expression',
            $this->dispatchCollectionGet(),
            'After flushing the per-member tag derived from book 1\'s author, the next request must be a MISS with fresh data.'
        );
    }

    #[Test]
    public function requestFailingReadAndWriteConditionsBypassesCacheLookupAndStore(): void
    {
        $this->dispatchCollectionGet();
        $this->updateBookTitle('Uncached title');

        self::assertStringContainsString(
            'Uncached title',
            $this->dispatchCollectionGet('https://example.com/_api/books?nocache=1'),
            'A request failing the readCondition must be processed freshly instead of served from cache.'
        );

        // `nocache` is not a declared filter/pagination parameter, so this request maps to the SAME
        // cache entry identifier as a plain GET - if the fresh output had been stored despite the
        // failing writeCondition, the next plain GET would show the new title instead of the cached one.
        self::assertStringContainsString(
            'First book',
            $this->dispatchCollectionGet(),
            'The response of a request failing the writeCondition must not overwrite the cache entry.'
        );
    }

    /**
     * `Book::$author` is a plain `ManyToOne` relation to `Author`, which carries no `@ApiResource`
     * of its own - it only ever appears nested inside a `Book` response. This proves the automatic
     * per-record `<table>_<uid>` tagging in `CacheTagSubscriber` is driven purely by which
     * `AbstractDomainObject` instances get serialized into the response, not by whether the related
     * entity is itself a resource: editing "Author A" - embedded in the "First book" collection
     * entry - invalidates the cached collection response even though nothing in its own right was
     * ever requested through the API.
     */
    #[Test]
    public function collectionResponseIsInvalidatedByUpdateOfNestedAuthorRecord(): void
    {
        // 1. MISS - fresh output nests "Author A" inside the "First book" entry.
        $firstOutput = $this->dispatchCollectionGet();
        self::assertStringContainsString('Author A', $firstOutput);

        // 2. Change the related author row behind t3api's back - a cache HIT must still serve the
        // stale, nested author name.
        $this->updateAuthorName('Author A', 'Changed author name');
        self::assertStringContainsString(
            'Author A',
            $this->dispatchCollectionGet(),
            'Second request must be served from cache and therefore still show the pre-update author name.'
        );

        // 3. Dispatch the invalidation event for the author record - next request must be a MISS
        // with the fresh, nested author name.
        $this->get(EventDispatcherInterface::class)->dispatch(new RecordUpdatedEvent(1, self::AUTHOR_TABLE));
        self::assertStringContainsString(
            'Changed author name',
            $this->dispatchCollectionGet(),
            'After invalidation the cache must be bypassed and the fresh, nested author name returned.'
        );
    }

    /**
     * Negative-case shape: book uid 1 embeds only "Author A" (author uid 1); book uid 2 embeds only
     * "Author B" (author uid 2, never requested here). Flushing author B's own `<table>_<uid>` tag
     * must NOT invalidate book 1's cached item response, since that response was never tagged with
     * it - only flushing author A's tag, the one actually nested inside book 1's response, may.
     * This proves nested tagging is precise per embedded record, not a blanket flush of the whole
     * related table.
     */
    #[Test]
    public function itemResponseIsInvalidatedOnlyByItsOwnNestedAuthorNotByAnUnrelatedOne(): void
    {
        // 1. MISS - fresh output for book 1 nests "Author A".
        $firstOutput = $this->dispatchBookItemGet(1);
        self::assertStringContainsString('Author A', $firstOutput);

        // 2. Change author A's row behind t3api's back - a cache HIT must still serve the stale,
        // nested author name.
        $this->updateAuthorName('Author A', 'Changed author A name');
        self::assertStringContainsString(
            'Author A',
            $this->dispatchBookItemGet(1),
            'Second request must be served from cache and therefore still show the pre-update author name.'
        );

        // 3. NEGATIVE - flush author B's tag (uid 2, embedded only in book 2, never requested here).
        // Book 1's cached item response must remain untouched: still a HIT, still the stale name.
        $this->get(EventDispatcherInterface::class)->dispatch(new RecordUpdatedEvent(2, self::AUTHOR_TABLE));
        self::assertStringContainsString(
            'Author A',
            $this->dispatchBookItemGet(1),
            'Flushing an unrelated author\'s tag must not invalidate a response that never embedded that author.'
        );

        // 4. POSITIVE - flush author A's own tag (uid 1, actually embedded in book 1's response).
        // The next request must be a MISS with the fresh, nested author name.
        $this->get(EventDispatcherInterface::class)->dispatch(new RecordUpdatedEvent(1, self::AUTHOR_TABLE));
        self::assertStringContainsString(
            'Changed author A name',
            $this->dispatchBookItemGet(1),
            'Flushing book 1\'s own nested author tag must invalidate its cached item response.'
        );
    }

    /**
     * Proves the cache-tag scoping fix: a membership-changing write's *automatic* flush target is
     * `<table>--collection` plus the record's own `<table>_<uid>` (see `FlushCacheAfterSaveListener`),
     * never the blanket `<table>` tag every response also carries - so an unrelated record's
     * membership-changing update can no longer evict a still-correct cached item response on the
     * same table, only the record's own update (via its own uid tag) or a plain update (via the
     * unchanged, uid-only plain-update path) still can.
     */
    #[Test]
    public function itemResponseSurvivesAnUnrelatedMembershipChangeButIsEvictedByItsOwnUpdate(): void
    {
        // 1. MISS - fresh output for book 1.
        $firstOutput = $this->dispatchBookItemGet(1);
        self::assertStringContainsString('First book', $firstOutput);

        // 2. Change book 1's row behind t3api's back - a cache HIT must still serve the stale title.
        $this->updateBookTitle('Changed title after unrelated update');
        self::assertStringContainsString(
            'First book',
            $this->dispatchBookItemGet(1),
            'Second request must be served from cache and therefore still show the pre-update title.'
        );

        // 3. NEGATIVE - dispatch a membership-changing update for an UNRELATED book (uid 2). Book 1's
        // cached item response must remain untouched: still a HIT, still the stale title.
        $this->get(EventDispatcherInterface::class)->dispatch(
            new RecordUpdatedEvent(2, self::TABLE, changesCollectionMembership: true)
        );
        self::assertStringContainsString(
            'First book',
            $this->dispatchBookItemGet(1),
            'A membership-changing update to an unrelated record must not evict this record\'s cached item response.'
        );

        // 4. POSITIVE - dispatch a membership-changing update for book 1 ITSELF. Its own
        // `<table>_<uid>` tag must evict the cached item response (own-uid-tag correctness
        // requirement), even though this response never carried the `<table>--collection` tag that
        // step 3's unrelated update flushed instead.
        $this->get(EventDispatcherInterface::class)->dispatch(
            new RecordUpdatedEvent(1, self::TABLE, changesCollectionMembership: true)
        );
        self::assertStringContainsString(
            'Changed title after unrelated update',
            $this->dispatchBookItemGet(1),
            'A membership-changing update to the record itself must evict its own cached item response.'
        );

        // 5. Change book 1's row behind t3api's back again - a cache HIT must still serve the title
        // from step 4, not the one written just now.
        $this->updateBookTitle('Changed title after plain update');
        self::assertStringContainsString(
            'Changed title after unrelated update',
            $this->dispatchBookItemGet(1),
            'Third request must be served from cache and therefore still show the previous, not the latest, title.'
        );

        // 6. REGRESSION GUARD - a plain (non-membership) update to book 1 must still evict its own
        // cached item response, exactly as before this change (the unchanged, uid-only path).
        $this->get(EventDispatcherInterface::class)->dispatch(new RecordUpdatedEvent(1, self::TABLE));
        self::assertStringContainsString(
            'Changed title after plain update',
            $this->dispatchBookItemGet(1),
            'A plain update to the record itself must still evict its own cached item response.'
        );
    }

    /**
     * Updates the row directly via SQL, bypassing t3api entirely, and detaches Extbase's
     * persistence session identity map so the next repository fetch cannot serve the stale
     * in-memory object it may have loaded earlier - without this, a `dispatchCollectionGet()`
     * call could return pre-update data purely because of Extbase's own object identity cache,
     * which would look identical to (and be mistaken for) a t3api response cache hit.
     */
    private function updateBookTitle(string $title): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE)
            ->update(self::TABLE, ['title' => $title], ['uid' => 1]);

        $this->get(PersistenceManagerInterface::class)->clearState();
    }

    /**
     * Updates an author row directly via SQL, bypassing t3api entirely, and detaches Extbase's
     * persistence session identity map for the same reason `updateBookTitle()` does.
     */
    private function updateAuthorName(string $currentName, string $newName): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::AUTHOR_TABLE)
            ->update(self::AUTHOR_TABLE, ['name' => $newName], ['name' => $currentName]);

        $this->get(PersistenceManagerInterface::class)->clearState();
    }

    /**
     * Deletes every fixture row directly via SQL, bypassing t3api entirely, and detaches
     * Extbase's persistence session identity map for the same reason `updateBookTitle()` does.
     */
    private function deleteAllBooks(): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE)->truncate(self::TABLE);

        $this->get(PersistenceManagerInterface::class)->clearState();
    }

    /**
     * Inserts a row directly via SQL, bypassing t3api entirely, and detaches Extbase's
     * persistence session identity map for the same reason `updateBookTitle()` does.
     */
    private function insertBook(string $title): int
    {
        $connection = $this->getConnectionPool()->getConnectionForTable(self::TABLE);
        $connection->insert(self::TABLE, ['pid' => 0, 'title' => $title]);
        $newUid = (int)$connection->lastInsertId();

        $this->get(PersistenceManagerInterface::class)->clearState();

        return $newUid;
    }

    private function dispatchCollectionGet(string $url = 'https://example.com/_api/books'): string
    {
        $symfonyRequest = SymfonyRequest::create($url, 'GET');
        $requestContext = (new RequestContext())->fromRequest($symfonyRequest);
        $response = new Response();

        return $this->get(Bootstrap::class)
            ->processOperationByRequest($requestContext, $symfonyRequest, $response);
    }

    private function dispatchBookItemGet(int $uid): string
    {
        $symfonyRequest = SymfonyRequest::create('https://example.com/_api/books/' . $uid, 'GET');
        $requestContext = (new RequestContext())->fromRequest($symfonyRequest);
        $response = new Response();

        return $this->get(Bootstrap::class)
            ->processOperationByRequest($requestContext, $symfonyRequest, $response);
    }

    /**
     * Writes the site configuration file directly instead of going through
     * `TYPO3\CMS\Core\Configuration\SiteWriter`, which only exists from TYPO3 13.1 onwards
     * (in earlier versions writing was a responsibility of `SiteConfiguration` itself) - this
     * keeps the test working across the whole TYPO3 12/13/14 support range.
     */
    private function writeSiteConfiguration(): void
    {
        $siteConfigurationDirectory = Environment::getConfigPath() . '/sites/' . self::SITE_IDENTIFIER;
        GeneralUtility::mkdir_deep($siteConfigurationDirectory);
        file_put_contents($siteConfigurationDirectory . '/config.yaml', Yaml::dump([
            'rootPageId' => 1,
            'base' => '/',
            'languages' => [
                0 => [
                    'title' => 'English',
                    'enabled' => true,
                    'languageId' => 0,
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                    'navigationTitle' => 'English',
                ],
            ],
            'routeEnhancers' => [
                'T3apiResourceEnhancer' => [
                    'type' => 'T3apiResourceEnhancer',
                    'basePath' => '_api',
                ],
            ],
        ], 99, 2));

        $this->flushSiteConfigurationCaches();
    }

    /**
     * `SiteConfiguration` caches its resolved sites under the fixed identifier `sites-configuration`
     * in both the `core` and `runtime` caches; a direct file write bypasses the invalidation that
     * `SiteWriter::write()`/`SiteConfiguration::write()` would otherwise trigger via
     * `SiteConfigurationChangedEvent`, so both entries are removed explicitly here.
     */
    private function flushSiteConfigurationCaches(): void
    {
        $cacheManager = $this->get(CacheManager::class);
        $cacheManager->getCache('core')->remove('sites-configuration');
        $cacheManager->getCache('runtime')->remove('sites-configuration');
    }
}
