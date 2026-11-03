<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Dispatcher\Bootstrap;
use SourceBroker\T3api\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Http\Response;

/**
 * `storagePid` limits records of the collection, item operations load a record from any page (like Extbase
 * `findByUid()`). Configuration from the Security documentation limits them with `security`, see
 * `T3apiTests\FunctionalTest\Domain\Model\StoredCategory` (storage page 3).
 */
class StoragePidDispatcherTest extends AbstractDispatcherTestCase
{
    private const TABLE = 'tx_functionaltest_domain_model_category';

    protected function setUp(): void
    {
        parent::setUp();
        $connection = $this->getConnectionPool()->getConnectionForTable(self::TABLE);
        $connection->insert(self::TABLE, ['uid' => 1, 'pid' => 3, 'title' => 'On storage page']);
        $connection->insert(self::TABLE, ['uid' => 2, 'pid' => 9, 'title' => 'On other page']);
    }

    #[Test]
    public function collectionContainsOnlyRecordsOfStoragePage(): void
    {
        $response = $this->dispatch('GET', '/stored-categories');

        self::assertSame([1], array_column(json_decode($response['body'], true)['hydra:member'], 'uid'));
    }

    #[Test]
    public function itemOperationWithoutSecurityLoadsRecordOfOtherPage(): void
    {
        $response = $this->dispatch('GET', '/unrestricted-stored-categories/2');

        self::assertSame(200, $response['status']);
        self::assertSame('On other page', json_decode($response['body'], true)['title']);
    }

    #[Test]
    public function securityAllowsRecordOfStoragePage(): void
    {
        self::assertSame(200, $this->dispatch('GET', '/stored-categories/1')['status']);
    }

    #[Test]
    public function securityDeniesRecordOfOtherPage(): void
    {
        self::assertSame(403, $this->dispatch('GET', '/stored-categories/2')['status']);
        self::assertSame(403, $this->dispatch('PATCH', '/stored-categories/2', '{"title": "Changed", "pid": 3}')['status']);
        self::assertSame(403, $this->dispatch('PUT', '/stored-categories/2', '{"title": "Changed"}')['status']);
        self::assertSame(403, $this->dispatch('DELETE', '/stored-categories/2')['status']);

        self::assertSame(['pid' => 9, 'title' => 'On other page', 'deleted' => 0], $this->fetchRecord(2));
    }

    #[Test]
    public function pidIsNotWritableWithDenormalizationGroups(): void
    {
        $response = $this->dispatch('PATCH', '/stored-categories/1', '{"title": "Changed", "pid": 9}');

        self::assertSame(200, $response['status']);
        self::assertSame(['pid' => 3, 'title' => 'Changed', 'deleted' => 0], $this->fetchRecord(1));
    }

    #[Test]
    public function putOfRecordOfStoragePageIsAllowed(): void
    {
        self::assertSame(200, $this->dispatch('PUT', '/stored-categories/1', '{"title": "Replaced"}')['status']);
        self::assertSame(['pid' => 3, 'title' => 'Replaced', 'deleted' => 0], $this->fetchRecord(1));
    }

    #[Test]
    public function newRecordIsStoredOnStoragePage(): void
    {
        $response = $this->dispatch('POST', '/stored-categories', '{"title": "New", "pid": 9}');

        self::assertSame(201, $response['status']);
        self::assertSame(['pid' => 3, 'title' => 'New', 'deleted' => 0], $this->fetchRecord(json_decode($response['body'], true)['uid']));
    }

    private function fetchRecord(int $uid): array
    {
        return $this->getConnectionPool()->getConnectionForTable(self::TABLE)
            ->select(['pid', 'title', 'deleted'], self::TABLE, ['uid' => $uid])->fetchAssociative();
    }

    /**
     * @return array{status: int, body: string}
     */
    private function dispatch(string $method, string $path, ?string $body = null): array
    {
        $request = SymfonyRequest::create(
            'https://example.com/_api' . $path,
            $method,
            [],
            [],
            [],
            $body !== null ? ['CONTENT_TYPE' => 'application/json'] : [],
            $body
        );
        $response = new Response();
        try {
            $body = $this->get(Bootstrap::class)
                ->processOperationByRequest((new RequestContext())->fromRequest($request), $request, $response);
        } catch (ExceptionInterface $exception) {
            return ['status' => $exception->getStatusCode(), 'body' => ''];
        }

        return ['status' => $response->getStatusCode(), 'body' => (string)$body];
    }
}
