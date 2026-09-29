<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;

/**
 * Full-cycle functional tests for query modifiers combined with the built-in filters, run against
 * the `Product` fixture resource:
 *
 * - `tags_title` is a `SearchFilter` on the MM relation `tags.title` - the Extbase query parser
 *   resolves it with a JOIN and relies on DISTINCT to keep one row per product (product 1 carries
 *   the tags "red" and "ruby", product 2 only "red", so `tags_title=r` joins product 1 twice),
 * - `order[...]` is the built-in `OrderFilter`,
 * - `fixedOrder` / `tieBreakOrder` / `fixedOrderAfterOrder` are `FixedOrderFilter` declarations
 *   (ordered-UIDs filter), the last one with `rankingPrecedence: afterOrderFilter`,
 * - `charLimit` / `maxTitleLength` are statement-based query modifiers contributing a WHERE
 *   condition; `charLimit` sorts alphabetically before `fixedOrder`, so it converts the query first.
 */
class QueryModifierCompositionDispatcherTest extends AbstractDispatcherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/products.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/categories.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tags.csv');
    }

    #[Test]
    public function toManyRelationFilterAloneReturnsEveryMatchingRecordOnce(): void
    {
        $response = $this->dispatchProductsGet(['tags_title' => 'r']);

        self::assertEqualsCanonicalizing([1, 2], $this->memberUids($response));
        self::assertSame(2, $response['hydra:totalItems']);
    }

    #[Test]
    public function orderedUidsRankingCombinedWithToManyRelationFilterReturnsEveryRecordOnce(): void
    {
        $response = $this->dispatchProductsGet(['tags_title' => 'r', 'fixedOrder' => '2,1,3']);

        self::assertSame([2, 1], $this->memberUids($response));
        self::assertSame(2, $response['hydra:totalItems']);
    }

    #[Test]
    public function orderedUidsRankingCombinedWithToManyRelationFilterPaginatesOverDistinctRecords(): void
    {
        $queryParams = ['tags_title' => 'r', 'fixedOrder' => '1,2', 'itemsPerPage' => 1];

        $firstPage = $this->dispatchProductsGet($queryParams + ['page' => 1]);
        $secondPage = $this->dispatchProductsGet($queryParams + ['page' => 2]);

        self::assertSame([1], $this->memberUids($firstPage));
        self::assertSame([2], $this->memberUids($secondPage));
        self::assertSame(2, $secondPage['hydra:totalItems']);
    }

    #[Test]
    public function statementModifierCombinedWithToManyRelationFilterReturnsEveryRecordOnce(): void
    {
        $response = $this->dispatchProductsGet(['tags_title' => 'r', 'maxTitleLength' => 6]);

        self::assertEqualsCanonicalizing([1, 2], $this->memberUids($response));
        self::assertSame(2, $response['hydra:totalItems']);
    }

    #[Test]
    public function orderedUidsRankingTakesPrecedenceOverOrderFilterByDefault(): void
    {
        $response = $this->dispatchProductsGet(['fixedOrder' => '5,1,6,2', 'order' => ['category' => 'asc']]);

        self::assertSame([5, 1, 6, 2], $this->memberUids($response));
    }

    /**
     * Without a common rule the outcome depended on whether another statement modifier converted
     * the query first: the Extbase ordering derived from `order[title]` survived that conversion
     * and turned the ranking into a mere tie-breaker ([4, 3, 2, 1] instead of [4, 2, 3, 1]).
     */
    #[Test]
    public function orderedUidsRankingTakesPrecedenceOverOrderFilterAfterAnotherModifierConvertedTheQuery(): void
    {
        $response = $this->dispatchProductsGet([
            'charLimit' => 6,
            'fixedOrder' => '4,2,3,1',
            'order' => ['title' => 'desc'],
        ]);

        self::assertSame([4, 2, 3, 1], $this->memberUids($response));
    }

    #[Test]
    public function orderedUidsRankingDeclaredAfterOrderFilterBreaksTiesOfExplicitOrdering(): void
    {
        $response = $this->dispatchProductsGet([
            'fixedOrderAfterOrder' => '5,1,6,2',
            'order' => ['category' => 'asc'],
        ]);

        self::assertSame([1, 2, 5, 6], $this->memberUids($response));
    }

    #[Test]
    public function orderedUidsRankingDeclaredAfterOrderFilterBreaksTiesAfterAnotherModifierConvertedTheQuery(): void
    {
        $response = $this->dispatchProductsGet([
            'charLimit' => 6,
            'fixedOrderAfterOrder' => '5,1,6,2',
            'order' => ['category' => 'asc'],
        ]);

        self::assertSame([1, 2, 5, 6], $this->memberUids($response));
    }

    /**
     * Symfony's Request::getQueryString() sorts only the top-level parameters - the keys nested in
     * `order[...]` keep the order of the request, so the client still decides which OrderFilter
     * property is the primary ordering. Products 1, 2 are in category 1 (Alfa, Brie), products
     * 5, 6 in category 2 (Ebony, Fig).
     */
    #[Test]
    public function orderFilterPropertiesFollowTheirOrderInTheRequest(): void
    {
        $categoryFirst = $this->dispatchProductsGet([
            'fixedOrderAfterOrder' => '1,5,2,6',
            'order' => ['category' => 'asc', 'title' => 'desc'],
        ]);
        $titleFirst = $this->dispatchProductsGet([
            'fixedOrderAfterOrder' => '1,5,2,6',
            'order' => ['title' => 'desc', 'category' => 'asc'],
        ]);

        self::assertSame([2, 1, 6, 5], $this->memberUids($categoryFirst));
        self::assertSame([6, 5, 2, 1], $this->memberUids($titleFirst));
    }

    /**
     * The same without any query modifier - the plain Extbase query path, unchanged by query
     * modifiers: products 298, 295, 292 are the last titles of category 1, 300, 299, 298 the last
     * titles overall.
     */
    #[Test]
    public function orderFilterPropertiesFollowTheirOrderInTheRequestWithoutQueryModifiers(): void
    {
        $categoryFirst = $this->dispatchProductsGet([
            'order' => ['category' => 'asc', 'title' => 'desc'],
            'itemsPerPage' => 3,
        ]);
        $titleFirst = $this->dispatchProductsGet([
            'order' => ['title' => 'desc', 'category' => 'asc'],
            'itemsPerPage' => 3,
        ]);

        self::assertSame([298, 295, 292], $this->memberUids($categoryFirst));
        self::assertSame([300, 299, 298], $this->memberUids($titleFirst));
    }

    #[Test]
    public function orderedUidsRankingDeclaredAfterOrderFilterStillRanksWithoutExplicitOrdering(): void
    {
        $response = $this->dispatchProductsGet(['fixedOrderAfterOrder' => '5,1,6,2']);

        self::assertSame([5, 1, 6, 2], $this->memberUids($response));
    }

    /**
     * Symfony's Request::getQueryString() sorts the parameters, so it is the parameter name that
     * decides which ranking is the primary one, not the position in the URL.
     */
    #[Test]
    public function primaryRankingFollowsAlphabeticalOrderOfParameterNamesNotQueryStringPosition(): void
    {
        $response = $this->dispatchProductsGet(['tieBreakOrder' => '3,2,1', 'fixedOrder' => '1,2,3']);

        self::assertSame([1, 2, 3], $this->memberUids($response));
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
