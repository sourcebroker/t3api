<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Filter;

use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Filter\AbstractOrderedUidsFilter;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Selector;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AbstractOrderedUidsFilterTest extends UnitTestCase
{
    #[Test]
    public function filterPropertyReturnsNullWhenNoLookupWasPerformed(): void
    {
        $query = $this->createMock(QueryInterface::class);
        $query->expects(self::never())->method('in');

        self::assertNull(
            $this->createFilter()->filterProperty('search', null, $query, $this->createApiFilter())
        );
    }

    #[Test]
    public function filterPropertyConstrainsQueryToResolvedUidsWithZeroGuard(): void
    {
        $constraint = $this->createMock(ConstraintInterface::class);
        $query = $this->createMock(QueryInterface::class);
        $query->expects(self::once())
            ->method('in')
            ->with('uid', [5, 3, 9, 0])
            ->willReturn($constraint);

        self::assertSame(
            $constraint,
            $this->createFilter()->filterProperty('search', [5, 3, 9], $query, $this->createApiFilter())
        );
    }

    #[Test]
    public function filterPropertyMatchesNothingWhenLookupReturnsNoUids(): void
    {
        $query = $this->createMock(QueryInterface::class);
        $query->expects(self::once())
            ->method('in')
            ->with('uid', [0])
            ->willReturn($this->createMock(ConstraintInterface::class));

        $this->createFilter()->filterProperty('search', [], $query, $this->createApiFilter());
    }

    #[Test]
    public function modifyQueryDoesNothingWhenNoUidsWereResolved(): void
    {
        $filter = $this->createFilter();
        $apiFilter = $this->createApiFilter();

        $query = $this->createMock(Query::class);
        $query->expects(self::never())->method('statement');

        $filter->filterProperty('search', null, $query, $apiFilter);
        $filter->modifyQuery($query, $apiFilter, $this->createOperation());
    }

    #[Test]
    public function modifyQueryDoesNothingWhenQueryIsNotExtbaseGenericQuery(): void
    {
        $filter = $this->createFilter();
        $apiFilter = $this->createApiFilter();

        $query = $this->createMock(QueryInterface::class);
        $query->method('in')->willReturn($this->createMock(ConstraintInterface::class));
        $query->expects(self::never())->method('getSource');

        $filter->filterProperty('search', [1, 2], $query, $apiFilter);
        $filter->modifyQuery($query, $apiFilter, $this->createOperation());
    }

    #[Test]
    public function modifyQueryAppliesFieldOrderingToConcreteQueryBuilder(): void
    {
        $filter = $this->createFilter();
        $apiFilter = $this->createApiFilter();

        $query = $this->createQueryMock();

        $doctrineQueryBuilder = $this->createMock(DoctrineQueryBuilder::class);
        $doctrineQueryBuilder->expects(self::once())
            ->method('orderBy')
            ->with('FIELD(`tx_foo_domain_model_item`.`uid`, 5,3,9)');

        $queryBuilder = $this->createQueryBuilderMock($doctrineQueryBuilder);

        $queryParser = $this->createMock(Typo3DbQueryParser::class);
        $queryParser->method('convertQueryToDoctrineQueryBuilder')->with($query)->willReturn($queryBuilder);
        GeneralUtility::addInstance(Typo3DbQueryParser::class, $queryParser);

        $query->expects(self::once())->method('statement')->with($queryBuilder);

        $filter->filterProperty('search', [5, 3, 9], $query, $apiFilter);
        $filter->modifyQuery($query, $apiFilter, $this->createOperation());
    }

    #[Test]
    public function modifyQueryAppendsOrderingAsTieBreakerWhenQueryAlreadyCarriesQueryBuilderStatement(): void
    {
        $filter = $this->createFilter();
        $apiFilter = $this->createApiFilter();

        $doctrineQueryBuilder = $this->createMock(DoctrineQueryBuilder::class);
        $doctrineQueryBuilder->expects(self::never())->method('orderBy');
        $doctrineQueryBuilder->expects(self::once())
            ->method('addOrderBy')
            ->with('FIELD(`tx_foo_domain_model_item`.`uid`, 7,8)');

        $queryBuilder = $this->createQueryBuilderMock($doctrineQueryBuilder);

        $query = $this->createQueryMock();
        $query->method('getStatement')->willReturn(new Statement($queryBuilder));
        $query->expects(self::never())->method('statement');

        $filter->filterProperty('search', [7, 8], $query, $apiFilter);
        $filter->modifyQuery($query, $apiFilter, $this->createOperation());
    }

    #[Test]
    public function modifyQueryTracksResolvedUidsPerApiFilterDeclaration(): void
    {
        $filter = $this->createFilter();
        $firstDeclaration = $this->createApiFilter();
        $secondDeclaration = $this->createApiFilter();

        $query = $this->createQueryMock();

        $doctrineQueryBuilder = $this->createMock(DoctrineQueryBuilder::class);
        $doctrineQueryBuilder->expects(self::once())
            ->method('orderBy')
            ->with('FIELD(`tx_foo_domain_model_item`.`uid`, 5,3)');

        $queryBuilder = $this->createQueryBuilderMock($doctrineQueryBuilder);

        $queryParser = $this->createMock(Typo3DbQueryParser::class);
        $queryParser->method('convertQueryToDoctrineQueryBuilder')->willReturn($queryBuilder);
        GeneralUtility::addInstance(Typo3DbQueryParser::class, $queryParser);

        $filter->filterProperty('search', [5, 3], $query, $firstDeclaration);
        $filter->filterProperty('search', [9], $query, $secondDeclaration);

        $filter->modifyQuery($query, $firstDeclaration, $this->createOperation());
    }

    private function createFilter(): AbstractOrderedUidsFilter
    {
        return new class () extends AbstractOrderedUidsFilter {
            protected function resolveOrderedUids($values, ApiFilter $apiFilter): ?array
            {
                return $values;
            }
        };
    }

    private function createApiFilter(): ApiFilter
    {
        return new ApiFilter('filterClass', 'search', 'strategy', []);
    }

    private function createOperation(): CollectionOperation
    {
        return $this->createMock(CollectionOperation::class);
    }

    /**
     * @return Query&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createQueryMock(): Query
    {
        $query = $this->createMock(Query::class);
        $query->method('in')->willReturn($this->createMock(ConstraintInterface::class));
        $query->method('getSource')->willReturn(new Selector('tx_foo_domain_model_item', null));

        return $query;
    }

    /**
     * @return QueryBuilder&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createQueryBuilderMock(DoctrineQueryBuilder $doctrineQueryBuilder): QueryBuilder
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getConcreteQueryBuilder')->willReturn($doctrineQueryBuilder);
        $queryBuilder->method('quoteIdentifier')
            ->with('tx_foo_domain_model_item.uid')
            ->willReturn('`tx_foo_domain_model_item`.`uid`');

        return $queryBuilder;
    }
}
