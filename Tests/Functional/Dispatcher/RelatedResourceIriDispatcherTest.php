<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;

/**
 * API resources often extend models of other extensions (e.g. EXT:news). Extbase creates related
 * objects with the class from the property type of the extended model, so they are not instances
 * of the API resource class. When serializer metadata declares the property with the API resource
 * class, related objects are serialized with `@id` of that API resource.
 *
 * Fixture: `Product` relations `category` (`Category`) and `tags` (`ObjectStorage<Tag>`) are declared
 * as `CategoryResource` / `ObjectStorage<TagResource>` in serializer YAML metadata of the fixture extension.
 */
class RelatedResourceIriDispatcherTest extends AbstractDispatcherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/products.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/categories.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tags.csv');
    }

    #[Test]
    public function relatedObjectHasIriOfApiResourceDeclaredInSerializerMetadata(): void
    {
        $product = $this->dispatchProductGet(1);

        self::assertSame('/_api/categories/1', $product['category']['@id'] ?? null);
    }

    #[Test]
    public function relatedObjectsInObjectStorageHaveIriOfApiResourceDeclaredInSerializerMetadata(): void
    {
        $product = $this->dispatchProductGet(1);

        self::assertSame(['/_api/tags/1', '/_api/tags/2'], array_column($product['tags'], '@id'));
    }

    #[Test]
    public function apiResourceItselfKeepsItsIri(): void
    {
        self::assertSame('/_api/products/1', $this->dispatchProductGet(1)['@id']);
    }

    private function dispatchProductGet(int $uid): array
    {
        return json_decode(
            $this->dispatchGet('https://example.com/_api/products/' . $uid),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
