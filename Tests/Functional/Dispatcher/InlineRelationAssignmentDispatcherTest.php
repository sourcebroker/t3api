<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Service\OperationResponseCache;
use SourceBroker\T3api\Service\SerializerService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Stream;

/**
 * Existing records of relations stored in the related table (inline `steps` with `foreign_field`, `images` of TCA
 * type `file`) can be assigned by UID only when they have no parent or already belong to the same parent property.
 */
class InlineRelationAssignmentDispatcherTest extends AbstractDispatcherTestCase
{
    private const RECIPE_TABLE = 'tx_functionaltest_domain_model_recipe';
    private const STEP_TABLE = 'tx_functionaltest_domain_model_recipestep';

    protected function setUp(): void
    {
        parent::setUp();

        $recipes = $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE);
        $recipes->insert(self::RECIPE_TABLE, ['uid' => 1, 'pid' => 0, 'title' => 'First recipe', 'steps' => 1, 'images' => 1]);
        $recipes->insert(self::RECIPE_TABLE, ['uid' => 2, 'pid' => 0, 'title' => 'Second recipe', 'steps' => 1, 'images' => 1]);

        $steps = $this->getConnectionPool()->getConnectionForTable(self::STEP_TABLE);
        $steps->insert(self::STEP_TABLE, ['uid' => 1, 'pid' => 0, 'title' => 'Step of first recipe', 'recipe' => 1]);
        $steps->insert(self::STEP_TABLE, ['uid' => 2, 'pid' => 0, 'title' => 'Step of second recipe', 'recipe' => 2]);
        $steps->insert(self::STEP_TABLE, ['uid' => 3, 'pid' => 0, 'title' => 'Step without recipe', 'recipe' => 0]);

        $references = $this->getConnectionPool()->getConnectionForTable('sys_file_reference');
        foreach ([
            [11, 1, self::RECIPE_TABLE, 'images'],
            [12, 2, self::RECIPE_TABLE, 'images'],
            [13, 1, 'tt_content', 'image'],
            [14, 1, self::RECIPE_TABLE, 'other'],
            [15, 0, '', ''],
        ] as [$uid, $uidForeign, $tableNames, $fieldName]) {
            $references->insert('sys_file_reference', [
                'uid' => $uid,
                'pid' => 0,
                'uid_local' => 1,
                'uid_foreign' => $uidForeign,
                'tablenames' => $tableNames,
                'fieldname' => $fieldName,
            ]);
        }
    }

    #[Test]
    #[DataProvider('assignableRecordProvider')]
    public function recordWithoutParentOrOfSameParentPropertyIsAssigned(string $property, int $uid, string $table, string $parentField): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', [$property => [$uid]]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(1, $this->fetchField($table, $uid, $parentField));
    }

    public static function assignableRecordProvider(): array
    {
        return [
            'inline record of same parent' => ['steps', 1, self::STEP_TABLE, 'recipe'],
            'inline record without parent' => ['steps', 3, self::STEP_TABLE, 'recipe'],
            'file reference of same parent property' => ['images', 11, 'sys_file_reference', 'uid_foreign'],
            'file reference without parent' => ['images', 15, 'sys_file_reference', 'uid_foreign'],
        ];
    }

    #[Test]
    #[DataProvider('notAssignableRecordProvider')]
    public function recordOfOtherParentIsRejected(string $property, int $uid, string $table, string $parentField, int $parentUid): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', [$property => [$uid]]);

        $body = (string)$response->getBody();
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(400, $response->getStatusCode(), $body);
        self::assertSame(
            sprintf('Record `%d` in path `%s` belongs to another record and can not be assigned', $uid, $property),
            $json['hydra:description']
        );
        self::assertSame($parentUid, $this->fetchField($table, $uid, $parentField));
    }

    public static function notAssignableRecordProvider(): array
    {
        return [
            'inline record of other parent' => ['steps', 2, self::STEP_TABLE, 'recipe', 2],
            'file reference of other parent' => ['images', 12, 'sys_file_reference', 'uid_foreign', 2],
            'file reference of record with same UID in other table' => ['images', 13, 'sys_file_reference', 'uid_foreign', 1],
            'file reference of other property of same parent' => ['images', 14, 'sys_file_reference', 'uid_foreign', 1],
        ];
    }

    #[Test]
    public function putKeepsInlineRecordsOfSameParent(): void
    {
        $response = $this->dispatch('PUT', '/_api/recipes/1', ['title' => 'Replaced', 'steps' => [1]]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(1, $this->fetchField(self::STEP_TABLE, 1, 'recipe'));
    }

    #[Test]
    public function newRecordAcceptsOnlyInlineRecordsWithoutParent(): void
    {
        $rejected = $this->dispatch('POST', '/_api/recipes', ['title' => 'New recipe', 'steps' => [1]]);

        self::assertSame(400, $rejected->getStatusCode(), (string)$rejected->getBody());
        self::assertSame(1, $this->fetchField(self::STEP_TABLE, 1, 'recipe'));

        $created = $this->dispatch('POST', '/_api/recipes', ['title' => 'New recipe', 'steps' => [3]]);
        $json = json_decode((string)$created->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $created->getStatusCode(), (string)$created->getBody());
        self::assertSame($json['uid'], $this->fetchField(self::STEP_TABLE, 3, 'recipe'));
        self::assertSame(3, $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)->count('*', self::RECIPE_TABLE, []));
    }

    private function fetchField(string $table, int $uid, string $field): int
    {
        return (int)$this->getConnectionPool()->getConnectionForTable($table)
            ->select([$field], $table, ['uid' => $uid])->fetchOne();
    }

    private function dispatch(string $method, string $path, array $body): ResponseInterface
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write(json_encode($body, JSON_THROW_ON_ERROR));
        $stream->rewind();

        $request = (new ServerRequest('https://example.com' . $path, $method, $stream))
            ->withHeader('Content-Type', 'application/json');

        $dispatcher = new Bootstrap(
            $this->get(SerializerService::class),
            $this->get(ApiResourceRepository::class),
            $this->get(SerializationContextBuilder::class),
            $this->get(DeserializationContextBuilder::class),
            $this->get(EventDispatcherInterface::class),
            $this->get(OperationResponseCache::class)
        );

        return $dispatcher->process($request);
    }
}
