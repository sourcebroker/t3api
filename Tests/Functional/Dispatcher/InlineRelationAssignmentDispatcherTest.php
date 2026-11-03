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
use SourceBroker\T3api\Service\FileTokenService;
use SourceBroker\T3api\Service\OperationResponseCache;
use SourceBroker\T3api\Service\SerializerService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Stream;

/**
 * Existing records of relations stored in the related table (inline `steps` with `foreign_field`, `images` of TCA
 * type `file`) can be assigned or updated by UID only when they already belong to the same parent property. New
 * records are created without UID. Records of other relations (`category`, MM `tags`) can be updated only when
 * they are related already - also in PUT, which resets the properties before deserialization.
 */
class InlineRelationAssignmentDispatcherTest extends AbstractDispatcherTestCase
{
    private const RECIPE_TABLE = 'tx_functionaltest_domain_model_recipe';
    private const STEP_TABLE = 'tx_functionaltest_domain_model_recipestep';
    private const CATEGORY_TABLE = 'tx_functionaltest_domain_model_category';
    private const TAG_TABLE = 'tx_functionaltest_domain_model_tag';

    protected function setUp(): void
    {
        parent::setUp();

        $recipes = $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE);
        $recipes->insert(self::RECIPE_TABLE, ['uid' => 1, 'pid' => 0, 'title' => 'First recipe', 'steps' => 1, 'images' => 1, 'image' => 1, 'category' => 1, 'tags' => 1]);
        $recipes->insert(self::RECIPE_TABLE, ['uid' => 2, 'pid' => 0, 'title' => 'Second recipe', 'steps' => 1, 'images' => 1]);

        foreach ([self::CATEGORY_TABLE, self::TAG_TABLE] as $table) {
            $connection = $this->getConnectionPool()->getConnectionForTable($table);
            $connection->insert($table, ['uid' => 1, 'pid' => 0, 'title' => 'Related']);
            $connection->insert($table, ['uid' => 2, 'pid' => 0, 'title' => 'Not related']);
        }
        $this->getConnectionPool()->getConnectionForTable('tx_functionaltest_recipe_tag_mm')
            ->insert('tx_functionaltest_recipe_tag_mm', ['uid_local' => 1, 'uid_foreign' => 1, 'sorting' => 1]);

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
            [16, 1, self::RECIPE_TABLE, 'image'],
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
    public function recordOfSameParentPropertyIsAssigned(string $property, int $uid, string $table, string $parentField): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', [$property => [$uid]]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(1, $this->fetchField($table, $uid, $parentField));
    }

    public static function assignableRecordProvider(): array
    {
        return [
            'inline record of same parent' => ['steps', 1, self::STEP_TABLE, 'recipe'],
            'file reference of same parent property' => ['images', 11, 'sys_file_reference', 'uid_foreign'],
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
            sprintf('Record `%d` in path `%s` does not belong to this record. Pass new records without `uid`', $uid, $property),
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
            'inline record without parent' => ['steps', 3, self::STEP_TABLE, 'recipe', 0],
            'file reference without parent' => ['images', 15, 'sys_file_reference', 'uid_foreign', 0],
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
    public function newRecordAcceptsOnlyNewInlineRecords(): void
    {
        foreach ([1, 3] as $existingUid) {
            $rejected = $this->dispatch('POST', '/_api/recipes', ['title' => 'New recipe', 'steps' => [$existingUid]]);
            self::assertSame(400, $rejected->getStatusCode(), (string)$rejected->getBody());
        }

        $created = $this->dispatch('POST', '/_api/recipes', ['title' => 'New recipe', 'steps' => [['title' => 'New step']]]);
        $json = json_decode((string)$created->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $created->getStatusCode(), (string)$created->getBody());
        self::assertSame(3, $this->getConnectionPool()->getConnectionForTable(self::RECIPE_TABLE)->count('*', self::RECIPE_TABLE, []));
        self::assertSame(
            $json['uid'],
            (int)$this->getConnectionPool()->getConnectionForTable(self::STEP_TABLE)
                ->select(['recipe'], self::STEP_TABLE, ['title' => 'New step'])->fetchOne()
        );
        self::assertSame(1, $this->fetchField(self::STEP_TABLE, 1, 'recipe'));
        self::assertSame(0, $this->fetchField(self::STEP_TABLE, 3, 'recipe'));
    }

    /**
     * Forms upload a file first (`sys_file`) and send it as a new reference by `uidLocal` together with the existing
     * references of the record.
     */
    #[Test]
    public function existingReferenceIsKeptAndNewReferenceIsCreatedFromUploadedFile(): void
    {
        // The test request is a backend request, so FAL evaluates storage permissions of a backend user.
        $this->getConnectionPool()->getConnectionForTable('be_users')->insert('be_users', [
            'uid' => 1,
            'pid' => 0,
            'username' => 'admin',
            'admin' => 1,
        ]);
        $this->setUpBackendUser(1);
        $this->getConnectionPool()->getConnectionForTable('sys_file_storage')->insert('sys_file_storage', [
            'uid' => 1,
            'pid' => 0,
            'name' => 'fileadmin',
            'driver' => 'Local',
            'configuration' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?><T3FlexForms><data><sheet index="sDEF"><language index="lDEF"><field index="basePath"><value index="vDEF">fileadmin/</value></field><field index="pathType"><value index="vDEF">relative</value></field><field index="caseSensitive"><value index="vDEF">1</value></field></language></sheet></data></T3FlexForms>',
            'is_browsable' => 1,
            'is_public' => 1,
            'is_writable' => 1,
            'is_online' => 1,
        ]);
        $this->getConnectionPool()->getConnectionForTable('sys_file')->insert('sys_file', [
            'uid' => 21,
            'pid' => 0,
            'storage' => 1,
            'type' => 2,
            'identifier' => '/user_upload/uploaded.jpg',
            'identifier_hash' => sha1('/user_upload/uploaded.jpg'),
            'folder_hash' => sha1('/user_upload/'),
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'name' => 'uploaded.jpg',
        ]);

        $fileToken = $this->get(FileTokenService::class)->createToken(21);
        $response = $this->dispatch('PATCH', '/_api/recipes/1', ['images' => [11, ['uidLocal' => 21, 'fileToken' => $fileToken]]]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(1, $this->fetchField('sys_file_reference', 11, 'uid_foreign'));
        $newReference = $this->getConnectionPool()->getConnectionForTable('sys_file_reference')
            ->select(['uid_foreign', 'tablenames', 'fieldname'], 'sys_file_reference', ['uid_local' => 21])
            ->fetchAssociative();
        self::assertSame(
            ['uid_foreign' => 1, 'tablenames' => self::RECIPE_TABLE, 'fieldname' => 'images'],
            ['uid_foreign' => (int)$newReference['uid_foreign'], 'tablenames' => $newReference['tablenames'], 'fieldname' => $newReference['fieldname']]
        );
    }

    #[Test]
    #[DataProvider('writeMethodProvider')]
    public function inlineRecordOfSameParentIsUpdated(string $method): void
    {
        $response = $this->dispatch($method, '/_api/recipes/1', [
            'title' => 'First recipe',
            'steps' => [['uid' => 1, 'title' => 'Changed step']],
        ]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame('Changed step', $this->fetchTitle(self::STEP_TABLE, 1));
        self::assertSame(1, $this->fetchField(self::STEP_TABLE, 1, 'recipe'));
    }

    #[Test]
    #[DataProvider('writeMethodProvider')]
    public function inlineRecordOfOtherParentIsNotUpdated(string $method): void
    {
        $response = $this->dispatch($method, '/_api/recipes/1', [
            'title' => 'First recipe',
            'steps' => [['uid' => 2, 'title' => 'Changed step']],
        ]);

        self::assertSame(400, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame('Step of second recipe', $this->fetchTitle(self::STEP_TABLE, 2));
        self::assertSame(2, $this->fetchField(self::STEP_TABLE, 2, 'recipe'));
    }

    #[Test]
    #[DataProvider('writeMethodProvider')]
    public function relatedRecordOfOtherRelationIsUpdated(string $method): void
    {
        $response = $this->dispatch($method, '/_api/recipes/1', [
            'title' => 'First recipe',
            'category' => ['uid' => 1, 'title' => 'Changed category'],
            'tags' => [['uid' => 1, 'title' => 'Changed tag']],
        ]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame('Changed category', $this->fetchTitle(self::CATEGORY_TABLE, 1));
        self::assertSame('Changed tag', $this->fetchTitle(self::TAG_TABLE, 1));
        self::assertSame(1, $this->fetchField(self::RECIPE_TABLE, 1, 'category'));
    }

    #[Test]
    #[DataProvider('notRelatedRecordProvider')]
    public function notRelatedRecordOfOtherRelationIsNotUpdated(string $method, int $recipeUid, array $body, string $table): void
    {
        $response = $this->dispatch($method, '/_api/recipes/' . $recipeUid, ['title' => 'Recipe'] + $body);

        self::assertSame(400, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame('Not related', $this->fetchTitle($table, 2));
    }

    public static function notRelatedRecordProvider(): array
    {
        $cases = [];
        foreach (['PATCH', 'PUT'] as $method) {
            $cases[$method . ' single relation'] = [$method, 1, ['category' => ['uid' => 2, 'title' => 'Changed']], self::CATEGORY_TABLE];
            $cases[$method . ' MM relation'] = [$method, 1, ['tags' => [['uid' => 2, 'title' => 'Changed']]], self::TAG_TABLE];
            $cases[$method . ' empty single relation'] = [$method, 2, ['category' => ['uid' => 2, 'title' => 'Changed']], self::CATEGORY_TABLE];
        }

        return $cases;
    }

    #[Test]
    #[DataProvider('notExistingRecordProvider')]
    public function notExistingRecordIsRejected(array $body, string $path): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', $body);

        $json = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(400, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(sprintf('Record `999` in path `%s` not found', $path), $json['hydra:description']);
    }

    public static function notExistingRecordProvider(): array
    {
        return [
            'inline record' => [['steps' => [999]], 'steps'],
            'file reference' => [['images' => [999]], 'images'],
            'cascade update' => [['steps' => [['uid' => 999, 'title' => 'Changed']]], 'steps'],
            'MM relation' => [['tags' => [999]], 'tags'],
        ];
    }

    #[Test]
    #[DataProvider('zeroValueProvider')]
    public function zeroClearsSingleRelation(int|string $value): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', ['category' => $value]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(0, $this->fetchField(self::RECIPE_TABLE, 1, 'category'));
    }

    public static function zeroValueProvider(): array
    {
        return [
            'integer' => [0],
            'string' => ['0'],
        ];
    }

    #[Test]
    #[DataProvider('emptyCollectionItemProvider')]
    public function emptyCollectionItemIsRejected(array $body, string $path): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', $body);

        $json = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(400, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(sprintf('Collection in path `%s` can not contain an empty value', $path), $json['hydra:description']);
    }

    public static function emptyCollectionItemProvider(): array
    {
        return [
            'zero in MM relation' => [['tags' => [0]], 'tags'],
            'zero string in inline relation' => [['steps' => [1, '0']], 'steps'],
            'zero in file references' => [['images' => [0]], 'images'],
        ];
    }

    #[Test]
    public function nullCollectionItemIsRejected(): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', ['steps' => [null]]);

        self::assertSame(400, $response->getStatusCode(), (string)$response->getBody());
    }

    #[Test]
    #[DataProvider('zeroValueProvider')]
    public function zeroRemovesReferenceOfSingleFileProperty(int|string $value): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', ['image' => $value]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->getRestrictions()->removeAll();
        self::assertSame(1, (int)$queryBuilder->select('deleted')->from('sys_file_reference')
            ->where($queryBuilder->expr()->eq('uid', 16))->executeQuery()->fetchOne());
    }

    #[Test]
    public function recordWithZeroUidIsCreated(): void
    {
        $response = $this->dispatch('PATCH', '/_api/recipes/1', ['steps' => [1, ['uid' => 0, 'title' => 'New step']]]);

        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());
        self::assertSame(
            1,
            (int)$this->getConnectionPool()->getConnectionForTable(self::STEP_TABLE)
                ->select(['recipe'], self::STEP_TABLE, ['title' => 'New step'])->fetchOne()
        );
    }

    public static function writeMethodProvider(): array
    {
        return [
            'PATCH' => ['PATCH'],
            'PUT' => ['PUT'],
        ];
    }

    private function fetchTitle(string $table, int $uid): string
    {
        return (string)$this->getConnectionPool()->getConnectionForTable($table)
            ->select(['title'], $table, ['uid' => $uid])->fetchOne();
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
