<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Base for filters that resolve the filter value to an ordered list of record UIDs — e.g. a
 * relevance-ranked result from an external search engine, a recommendation service or a curated
 * list — and want that order reflected in the collection result.
 *
 * A concrete filter only implements resolveOrderedUids(). This base then:
 *  - constrains the collection to those UIDs (uid IN (...)) via filterProperty(), and
 *  - as a QueryModifierInterface, re-applies the order as ORDER BY FIELD(uid, ...) once t3api has
 *    combined every filter's constraints (Extbase's ordering API cannot express FIELD()).
 *
 * The FIELD ordering is handed to the query as a Doctrine QueryBuilder statement; it survives
 * pagination and the count query because AbstractCollectionResponse gives the paginated query its
 * own QueryBuilder clone.
 *
 * Several such filters compose: each one's uid IN (...) constraint applies (the collection is the
 * intersection of all matches), the first modifier's ranking becomes the primary ordering and the
 * following ones are appended as tie-breakers on the already-present QueryBuilder statement.
 */
abstract class AbstractOrderedUidsFilter extends AbstractFilter implements QueryModifierInterface
{
    /**
     * Keyed by the ApiFilter declaration (spl_object_id): filters are singletons, so a resource
     * declaring one filter class under several parameter names shares a single instance —
     * per-declaration keying keeps the resolved UID lists from overwriting each other between
     * filterProperty() and modifyQuery().
     *
     * @var array<int, int[]>
     */
    private array $orderedUidsPerDeclaration = [];

    /**
     * Resolve the matching record UIDs, in the order they should appear, for the filter value(s).
     *
     * @param mixed $values the raw filter value(s) from the request
     * @return int[]|null null → no lookup performed (filter inactive, matches everything);
     *                    [] → lookup performed but nothing matched (matches nothing);
     *                    int[] → matching UIDs in the order to apply
     */
    abstract protected function resolveOrderedUids($values, ApiFilter $apiFilter): ?array;

    public function filterProperty(
        string $property,
        $values,
        QueryInterface $query,
        ApiFilter $apiFilter
    ): ?ConstraintInterface {
        $uids = $this->resolveOrderedUids($values, $apiFilter);
        if ($uids === null) {
            unset($this->orderedUidsPerDeclaration[spl_object_id($apiFilter)]);

            return null;
        }

        $this->orderedUidsPerDeclaration[spl_object_id($apiFilter)] = $uids;

        // The appended 0 guards the empty case: in('uid', []) is invalid SQL; uid 0 never matches.
        return $query->in('uid', array_merge($uids, [0]));
    }

    public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void
    {
        $orderedUids = $this->orderedUidsPerDeclaration[spl_object_id($apiFilter)] ?? [];
        unset($this->orderedUidsPerDeclaration[spl_object_id($apiFilter)]);

        if ($orderedUids === [] || !$query instanceof Query) {
            return;
        }

        $statement = $query->getStatement();
        if ($statement !== null && $statement->getStatement() instanceof QueryBuilder) {
            // An earlier query modifier already rewrote this query into a QueryBuilder statement.
            // Reconverting the QOM would silently discard its work — append this ordering to the
            // existing builder instead, as a tie-breaker after the earlier modifier's ordering.
            $queryBuilder = $statement->getStatement();
            $queryBuilder->getConcreteQueryBuilder()
                ->addOrderBy($this->buildFieldOrderExpression($queryBuilder, $query, $orderedUids));

            return;
        }

        $queryBuilder = GeneralUtility::makeInstance(Typo3DbQueryParser::class)
            ->convertQueryToDoctrineQueryBuilder($query);

        // The ordering goes to the concrete Doctrine QueryBuilder: TYPO3's facade would quote the
        // whole FIELD() expression as an identifier, and orderBy() on the concrete builder also
        // replaces any ordering Typo3DbQueryParser derived from the Extbase query.
        $queryBuilder->getConcreteQueryBuilder()
            ->orderBy($this->buildFieldOrderExpression($queryBuilder, $query, $orderedUids));

        $query->statement($queryBuilder);
    }

    /**
     * @param int[] $orderedUids
     */
    private function buildFieldOrderExpression(QueryBuilder $queryBuilder, Query $query, array $orderedUids): string
    {
        return 'FIELD('
            . $queryBuilder->quoteIdentifier($this->getTableName($query) . '.uid') . ', '
            . implode(',', array_map('intval', $orderedUids))
            . ')';
    }
}
