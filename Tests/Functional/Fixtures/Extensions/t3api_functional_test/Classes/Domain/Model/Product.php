<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use SourceBroker\T3api\Annotation\ApiFilter;
use SourceBroker\T3api\Annotation\ApiResource;
use SourceBroker\T3api\Filter\OrderFilter;
use SourceBroker\T3api\Filter\SearchFilter;
use T3apiTests\FunctionalTest\Filter\CategoryOrderFilter;
use T3apiTests\FunctionalTest\Filter\FixedOrderFilter;
use T3apiTests\FunctionalTest\Filter\MaxTitleLengthFilter;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Dedicated resource for filter and pagination functional tests — kept separate from `Book`
 * (the response-cache resource) so that each feature area owns its resource configuration and
 * fixture data, and changes to one never ripple into the other's tests.
 *
 * @ApiResource(
 *     attributes={
 *         "pagination_client_items_per_page": true
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/products"
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/products/{id}"
 *         }
 *     }
 * )
 *
 * @ApiFilter(
 *     FixedOrderFilter::class,
 *     arguments={"parameterName": "fixedOrder"}
 * )
 *
 * @ApiFilter(
 *     FixedOrderFilter::class,
 *     arguments={"parameterName": "tieBreakOrder"}
 * )
 *
 * @ApiFilter(
 *     FixedOrderFilter::class,
 *     arguments={"parameterName": "fixedOrderAfterOrder", "rankingPrecedence": "afterOrderFilter"}
 * )
 *
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={"tags.title": "partial"}
 * )
 *
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={"title", "category"}
 * )
 *
 * @ApiFilter(
 *     MaxTitleLengthFilter::class,
 *     arguments={"parameterName": "maxTitleLength"}
 * )
 *
 * Query modifiers run in the sorted order of their parameter names (Symfony normalizes the
 * query string) - `charLimit` sorts before `fixedOrder`, `maxTitleLength` after it, so the two
 * declarations cover both hand-off directions between the statement modifiers.
 *
 * @ApiFilter(
 *     MaxTitleLengthFilter::class,
 *     arguments={"parameterName": "charLimit"}
 * )
 *
 * @ApiFilter(
 *     CategoryOrderFilter::class,
 *     arguments={"parameterName": "categoryOrder"}
 * )
 */
class Product extends AbstractEntity
{
    protected string $title = '';

    protected ?Category $category = null;

    /**
     * @var ObjectStorage<Tag>
     */
    protected ObjectStorage $tags;

    public function __construct()
    {
        $this->tags = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return ObjectStorage<Tag>
     */
    public function getTags(): ObjectStorage
    {
        return $this->tags;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }
}
