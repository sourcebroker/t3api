<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;

/**
 * Full-cycle functional tests for collection pagination and query modifiers, run against the
 * dedicated `Product` fixture resource (300 records: uids 1-12 hand-crafted for the filter
 * scenarios — 3 categories, varied title lengths — and uids 13-300 generated volume for
 * pagination and future load-oriented tests), in both query flavours:
 *
 * - a plain QOM query (no filter → no statement), and
 * - a query rewritten to a Doctrine QueryBuilder statement by `FixedOrderFilter`, a concrete
 *   `AbstractOrderedUidsFilter` implementation from the fixture extension (`?fixedOrder=9,4,7`
 *   plays the role of an external search engine's relevance-ranked UID list).
 *
 * The second-page cases are the regression guard for the shared-QueryBuilder bug: without the
 * QueryBuilder clone in `AbstractCollectionResponse::applyPagination()` the members query's
 * limit/offset leaked into the count query and `hydra:totalItems` collapsed to 0 on page 2.
 */
class CollectionPaginationDispatcherTest extends AbstractDispatcherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/products.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/categories.csv');
    }

    #[Test]
    public function plainCollectionIsPaginatedWithStableTotalItemsOnEveryPage(): void
    {
        $firstPage = $this->dispatchProductsGet(['itemsPerPage' => 125, 'page' => 1]);
        $secondPage = $this->dispatchProductsGet(['itemsPerPage' => 125, 'page' => 2]);
        $thirdPage = $this->dispatchProductsGet(['itemsPerPage' => 125, 'page' => 3]);

        self::assertCount(125, $firstPage['hydra:member']);
        self::assertCount(125, $secondPage['hydra:member']);
        self::assertCount(50, $thirdPage['hydra:member']);
        self::assertSame(300, $firstPage['hydra:totalItems']);
        self::assertSame(300, $secondPage['hydra:totalItems']);
        self::assertSame(300, $thirdPage['hydra:totalItems']);
        self::assertEqualsCanonicalizing(
            range(1, 300),
            array_merge(
                $this->memberUids($firstPage),
                $this->memberUids($secondPage),
                $this->memberUids($thirdPage)
            ),
            'All pages together must cover every fixture record exactly once.'
        );
    }

    #[Test]
    public function orderedUidsFilterReturnsMembersInResolvedOrderOnBothPages(): void
    {
        $queryParams = ['fixedOrder' => '11,2,9,4,7,6', 'itemsPerPage' => 3];

        $firstPage = $this->dispatchProductsGet($queryParams + ['page' => 1]);
        $secondPage = $this->dispatchProductsGet($queryParams + ['page' => 2]);

        self::assertSame([11, 2, 9], $this->memberUids($firstPage));
        self::assertSame([4, 7, 6], $this->memberUids($secondPage));
        self::assertSame(6, $firstPage['hydra:totalItems']);
        self::assertSame(
            6,
            $secondPage['hydra:totalItems'],
            'totalItems must stay the full match count on the second page - if the paginated query'
            . ' shared its QueryBuilder with the count query, the leaked limit/offset would skip'
            . ' the single COUNT(*) row and collapse totalItems to 0.'
        );
    }

    #[Test]
    public function orderedUidsFilterRestrictsCollectionToResolvedUidsKeepingTheirOrder(): void
    {
        $response = $this->dispatchProductsGet(['fixedOrder' => '9,3']);

        self::assertSame([9, 3], $this->memberUids($response));
        self::assertSame(2, $response['hydra:totalItems']);
    }

    /**
     * `fixedOrder` and `tieBreakOrder` are two declarations of the same FixedOrderFilter class
     * (one singleton instance, state keyed per declaration). Their `uid IN (...)` constraints
     * intersect and the ranking of the first filter in the query string stays the primary
     * ordering — the second one appends to the already-present QueryBuilder statement instead
     * of overwriting it.
     */
    #[Test]
    public function twoOrderedUidsFiltersIntersectConstraintsAndFirstRequestedRankingStaysPrimary(): void
    {
        $response = $this->dispatchProductsGet(['fixedOrder' => '4,3,2,1', 'tieBreakOrder' => '1,2,3']);

        self::assertSame([3, 2, 1], $this->memberUids($response));
        self::assertSame(3, $response['hydra:totalItems']);
    }

    /**
     * `MaxTitleLengthFilter` contributes a WHERE condition the QOM cannot express:
     * `LENGTH(title) <= 6` keeps "Brie" (uid 2) and "Alfa" (uid 1) of the requested UIDs and
     * drops "Hazelnut" (uid 8) and "Grapefruit" (uid 7). Declared second in the query string, it
     * must append its condition to the QueryBuilder statement left by the ordered-UIDs filter —
     * and the count query behind totalItems must see that condition too.
     */
    #[Test]
    public function statementModifierAddingWhereConditionComposesWithEarlierOrderedUidsRanking(): void
    {
        $response = $this->dispatchProductsGet(['fixedOrder' => '8,2,7,1', 'maxTitleLength' => 6]);

        self::assertSame([2, 1], $this->memberUids($response));
        self::assertSame(2, $response['hydra:totalItems']);
    }

    /**
     * Same combination with the parameter order reversed: now the WHERE-contributing modifier
     * converts the query first and the ordered-UIDs filter appends its FIELD() ranking to the
     * already-present QueryBuilder statement. Both hand-off directions must yield the same result.
     */
    #[Test]
    public function orderedUidsRankingComposesWithEarlierStatementModifierAddingWhereCondition(): void
    {
        $response = $this->dispatchProductsGet(['maxTitleLength' => 6, 'fixedOrder' => '8,2,7,1']);

        self::assertSame([2, 1], $this->memberUids($response));
        self::assertSame(2, $response['hydra:totalItems']);
    }

    /**
     * `CategoryOrderFilter` applies an ordering WITH ties (`ORDER BY category`; products 4 and 2
     * share category 1, products 8 and 6 share category 2), so the ordered-UIDs ranking appended
     * afterwards observably breaks the ties within each category group. This discriminates all
     * three possible behaviours of the second modifier: tie-breaking append → [4, 2, 8, 6];
     * destructive statement overwrite → [8, 4, 6, 2]; no effect at all → [2, 4, 6, 8].
     */
    #[Test]
    public function orderedUidsRankingBreaksTiesOfEarlierStatementOrdering(): void
    {
        $response = $this->dispatchProductsGet(['categoryOrder' => 1, 'fixedOrder' => '8,4,6,2']);

        self::assertSame([4, 2, 8, 6], $this->memberUids($response));
        self::assertSame(4, $response['hydra:totalItems']);
    }

    #[Test]
    public function orderedUidsFilterMatchingNothingReturnsEmptyCollection(): void
    {
        $response = $this->dispatchProductsGet(['fixedOrder' => 'none']);

        self::assertSame([], $response['hydra:member']);
        self::assertSame(0, $response['hydra:totalItems']);
    }

    private function dispatchProductsGet(array $queryParams): array
    {
        return json_decode(
            $this->dispatchGet('https://example.com/_api/products?' . http_build_query($queryParams)),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @return int[]
     */
    private function memberUids(array $decodedResponse): array
    {
        return array_map(
            static fn(array $member): int => $member['uid'],
            $decodedResponse['hydra:member']
        );
    }
}
