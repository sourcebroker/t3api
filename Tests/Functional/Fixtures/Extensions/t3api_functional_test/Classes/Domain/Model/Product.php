<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use SourceBroker\T3api\Annotation\ApiFilter;
use SourceBroker\T3api\Annotation\ApiResource;
use T3apiTests\FunctionalTest\Filter\CategoryOrderFilter;
use T3apiTests\FunctionalTest\Filter\FixedOrderFilter;
use T3apiTests\FunctionalTest\Filter\MaxTitleLengthFilter;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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
 *     MaxTitleLengthFilter::class,
 *     arguments={"parameterName": "maxTitleLength"}
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }
}
