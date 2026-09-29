<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use SourceBroker\T3api\Domain\Model\ApiFilter;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
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
 * The FIELD ordering is added to the query's Doctrine QueryBuilder statement shared by all query
 * modifiers (see StatementQueryBuilderProvider); it survives pagination and the count query because
 * AbstractCollectionResponse gives the paginated query its own QueryBuilder clone.
 *
 * By default the ranking precedes the orderings requested through OrderFilter (`order[...]`), which
 * then only break its ties; the `rankingPrecedence` argument of @ApiFilter set to `afterOrderFilter`
 * turns this around (see RankingPrecedence).
 *
 * Several such filters compose: each one's uid IN (...) constraint applies (the collection is the
 * intersection of all matches) and the rankings follow the order in which the modifiers run - the
 * sorted order of their parameter names (see CommonRepository::findFiltered()).
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

        $queryBuilder = $this->getOrCreateStatementQueryBuilder($query);
        $rankingExpression = $this->buildFieldOrderExpression($queryBuilder, $query, $orderedUids);

        if ($this->getRankingPrecedence($apiFilter) === RankingPrecedence::AfterOrderFilter) {
            GeneralUtility::makeInstance(StatementQueryBuilderProvider::class)
                ->addOrderingAfterExtbaseOrderings($queryBuilder, $rankingExpression);

            return;
        }

        // The concrete Doctrine builder takes the expression as is: TYPO3's facade would quote
        // the whole FIELD() expression as an identifier.
        $queryBuilder->getConcreteQueryBuilder()->addOrderBy($rankingExpression);
    }

    private function getRankingPrecedence(ApiFilter $apiFilter): RankingPrecedence
    {
        $configuredPrecedence = (string)($apiFilter->getArgument('rankingPrecedence')
            ?? RankingPrecedence::BeforeOrderFilter->value);
        $rankingPrecedence = RankingPrecedence::tryFrom($configuredPrecedence);
        if ($rankingPrecedence === null) {
            $supportedPrecedences = array_map(
                static fn(RankingPrecedence $precedence): string => $precedence->value,
                RankingPrecedence::cases()
            );

            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown `rankingPrecedence` `%s` of filter parameter `%s`, expected one of: %s.',
                    $configuredPrecedence,
                    $apiFilter->getParameterName(),
                    implode(', ', $supportedPrecedences)
                ),
                1790627277671
            );
        }

        return $rankingPrecedence;
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
