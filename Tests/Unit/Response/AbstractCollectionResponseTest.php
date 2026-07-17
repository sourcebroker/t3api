<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Response;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\Pagination;
use SourceBroker\T3api\Response\AbstractCollectionResponse;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AbstractCollectionResponseTest extends UnitTestCase
{
    #[Test]
    public function applyPaginationReturnsSameQueryInstanceWhenPaginationIsDisabled(): void
    {
        $query = $this->createQuery();
        $response = $this->createResponse($query, $this->createOperation(false));

        self::assertSame($query, $response->callApplyPagination());
    }

    #[Test]
    public function applyPaginationAppliesLimitAndOffsetOnQueryClone(): void
    {
        $query = $this->createQuery();
        $response = $this->createResponse($query, $this->createOperation(true, 10, 20));

        $paginatedQuery = $response->callApplyPagination();

        self::assertNotSame($query, $paginatedQuery);
        self::assertSame(10, $paginatedQuery->getLimit());
        self::assertSame(20, $paginatedQuery->getOffset());
        self::assertNull($query->getLimit());
    }

    #[Test]
    public function applyPaginationGivesQueryBuilderBackedStatementItsOwnQueryBuilderClone(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createQuery();
        $query->statement($queryBuilder, ['foo' => 'bar']);

        $response = $this->createResponse($query, $this->createOperation(true, 10, 20));

        $paginatedQuery = $response->callApplyPagination();
        $paginatedStatement = $paginatedQuery->getStatement();

        self::assertInstanceOf(QueryBuilder::class, $paginatedStatement->getStatement());
        self::assertNotSame($queryBuilder, $paginatedStatement->getStatement());
        self::assertSame(['foo' => 'bar'], $paginatedStatement->getBoundVariables());
        self::assertSame($queryBuilder, $query->getStatement()->getStatement());
    }

    #[Test]
    public function applyPaginationLeavesRawSqlStatementUntouched(): void
    {
        $query = $this->createQuery();
        $query->statement('SELECT * FROM tx_foo_domain_model_item');

        $response = $this->createResponse($query, $this->createOperation(true, 10, 20));

        $paginatedQuery = $response->callApplyPagination();

        self::assertSame($query->getStatement(), $paginatedQuery->getStatement());
    }

    private function createResponse(QueryInterface $query, CollectionOperation $operation): CollectionResponseFixture
    {
        return new CollectionResponseFixture($operation, Request::create('/items'), $query);
    }

    private function createOperation(
        bool $paginationEnabled,
        int $itemsPerPage = 10,
        int $offset = 0
    ): CollectionOperation {
        $pagination = $this->createMock(Pagination::class);
        $pagination->method('setParametersFromRequest')->willReturnSelf();
        $pagination->method('isEnabled')->willReturn($paginationEnabled);
        $pagination->method('getNumberOfItemsPerPage')->willReturn($itemsPerPage);
        $pagination->method('getOffset')->willReturn($offset);

        $operation = $this->createMock(CollectionOperation::class);
        $operation->method('getPagination')->willReturn($pagination);

        return $operation;
    }

    private function createQuery(): Query
    {
        return new Query(
            $this->createMock(DataMapFactory::class),
            $this->createMock(PersistenceManagerInterface::class),
            new QueryObjectModelFactory(),
            $this->createMock(ContainerInterface::class)
        );
    }
}

final class CollectionResponseFixture extends AbstractCollectionResponse
{
    public static function getOpenApiSchema(string $membersReference): Schema
    {
        return Schema::object();
    }

    public function callApplyPagination(): QueryInterface
    {
        return $this->applyPagination();
    }
}
