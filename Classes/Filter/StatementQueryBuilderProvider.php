<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Filter;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Owns the Doctrine QueryBuilder statement which query modifiers (see QueryModifierInterface) share
 * once the Extbase QOM cannot express what they need, so that several modifiers compose instead of
 * overwriting each other's work:
 *
 *  - The statement is converted from the fully constrained Extbase query once; every later modifier
 *    gets the very same builder.
 *  - Constraints on to-many relations (e.g. `tags.title`) make Extbase JOIN the relation and rely on
 *    DISTINCT, which Extbase does not apply to statements - neither for the members nor for the
 *    count query. Such a query is therefore converted to `uid IN (<constrained query>)` over a
 *    join-free outer query, keeping one row per record for members and count alike.
 *  - Orderings coming from the Extbase query (OrderFilter, `order[...]`) are moved to the end of the
 *    ORDER BY clause: orderings contributed by query modifiers take precedence over them, except for
 *    those added through addOrderingAfterExtbaseOrderings(), which follow them.
 *    CommonRepository::findFiltered() calls applyDeferredOrderings() after the last modifier.
 */
final class StatementQueryBuilderProvider implements SingletonInterface
{
    private const SUBQUERY_PARAMETER_PREFIX = 't3apiUidSubquery_';

    /**
     * @var \WeakMap<QueryBuilder, array{extbaseOrderings: string[], trailingOrderings: string[]}>
     */
    private \WeakMap $deferredOrderingsPerQueryBuilder;

    public function __construct()
    {
        $this->deferredOrderingsPerQueryBuilder = new \WeakMap();
    }

    public function getOrCreateQueryBuilder(Query $query): QueryBuilder
    {
        $existingQueryBuilder = $this->findStatementQueryBuilder($query);
        if ($existingQueryBuilder instanceof QueryBuilder) {
            return $existingQueryBuilder;
        }

        $queryBuilder = $this->convertToQueryBuilder($query);
        $this->deferredOrderingsPerQueryBuilder[$queryBuilder] = [
            'extbaseOrderings' => $this->takeOrderings($queryBuilder),
            'trailingOrderings' => [],
        ];
        $query->statement($queryBuilder);

        return $queryBuilder;
    }

    /**
     * Adds an ORDER BY expression which follows the orderings of the Extbase query (OrderFilter)
     * instead of preceding them.
     */
    public function addOrderingAfterExtbaseOrderings(QueryBuilder $queryBuilder, string $orderByExpression): void
    {
        if (!isset($this->deferredOrderingsPerQueryBuilder[$queryBuilder])) {
            // A statement not created here still carries the Extbase orderings in place.
            $this->addOrderingPart($queryBuilder, $orderByExpression);

            return;
        }

        $deferredOrderings = $this->deferredOrderingsPerQueryBuilder[$queryBuilder];
        $deferredOrderings['trailingOrderings'][] = $orderByExpression;
        $this->deferredOrderingsPerQueryBuilder[$queryBuilder] = $deferredOrderings;
    }

    public function applyDeferredOrderings(QueryInterface $query): void
    {
        $queryBuilder = $this->findStatementQueryBuilder($query);
        if (!$queryBuilder instanceof QueryBuilder || !isset($this->deferredOrderingsPerQueryBuilder[$queryBuilder])) {
            return;
        }

        $deferredOrderings = $this->deferredOrderingsPerQueryBuilder[$queryBuilder];
        unset($this->deferredOrderingsPerQueryBuilder[$queryBuilder]);

        foreach ([...$deferredOrderings['extbaseOrderings'], ...$deferredOrderings['trailingOrderings']] as $ordering) {
            $this->addOrderingPart($queryBuilder, $ordering);
        }
    }

    /**
     * Stored ORDER BY parts carry their direction (`tx_foo.title DESC`). The direction is passed on
     * separately: Doctrine DBAL 3 (TYPO3 12) appends `ASC` to an expression added without one.
     */
    private function addOrderingPart(QueryBuilder $queryBuilder, string $ordering): void
    {
        $concreteQueryBuilder = $queryBuilder->getConcreteQueryBuilder();
        if (preg_match('/^(.+)\s+(ASC|DESC)$/i', $ordering, $matches) === 1) {
            $concreteQueryBuilder->addOrderBy($matches[1], strtoupper($matches[2]));

            return;
        }

        $concreteQueryBuilder->addOrderBy($ordering);
    }

    private function findStatementQueryBuilder(QueryInterface $query): ?QueryBuilder
    {
        $statement = $query instanceof Query ? $query->getStatement() : null;
        $queryBuilder = $statement?->getStatement();

        return $queryBuilder instanceof QueryBuilder ? $queryBuilder : null;
    }

    private function convertToQueryBuilder(Query $query): QueryBuilder
    {
        $queryParser = GeneralUtility::makeInstance(Typo3DbQueryParser::class);
        $queryBuilder = $queryParser->convertQueryToDoctrineQueryBuilder($query);

        if (!$queryParser->isDistinctQuerySuggested()) {
            return $queryBuilder;
        }

        return $this->wrapIntoUidSubquery($query, $queryBuilder);
    }

    /**
     * The outer query is converted from the same Extbase query without its constraint: it keeps
     * the source table, the TYPO3 constraints (enable fields, storage pages, language) and the
     * orderings, including the joins nested orderings need.
     */
    private function wrapIntoUidSubquery(Query $query, QueryBuilder $joiningQueryBuilder): QueryBuilder
    {
        $tableName = $this->getTableName($query);

        $outerQueryParser = GeneralUtility::makeInstance(Typo3DbQueryParser::class);
        $outerQueryBuilder = $outerQueryParser->convertQueryToDoctrineQueryBuilder((clone $query)->matching(null));
        if ($outerQueryParser->isDistinctQuerySuggested()) {
            throw new \LogicException(
                sprintf(
                    'Orderings of `%s` by a to-many relation property (`%s`) cannot be combined with a query modifier.',
                    $query->getType(),
                    implode('`, `', array_keys($query->getOrderings()))
                ),
                1790627277248
            );
        }

        $this->takeOrderings($joiningQueryBuilder);
        $joiningQueryBuilder->select($tableName . '.uid');
        [$subquerySql, $subqueryParameters, $subqueryParameterTypes] = $this->prefixParameters($joiningQueryBuilder);

        $outerQueryBuilder->andWhere(
            $outerQueryBuilder->quoteIdentifier($tableName . '.uid') . ' IN (' . $subquerySql . ')'
        );
        $outerQueryBuilder->setParameters(
            $outerQueryBuilder->getParameters() + $subqueryParameters,
            $outerQueryBuilder->getParameterTypes() + $subqueryParameterTypes
        );

        return $outerQueryBuilder;
    }

    /**
     * Both builders name their parameters `dcValue1`, `dcValue2`, ... - the subquery's ones get a
     * prefix so they cannot collide with the parameters of the outer query.
     *
     * @return array{0: string, 1: array<string, mixed>, 2: array<string, mixed>}
     */
    private function prefixParameters(QueryBuilder $queryBuilder): array
    {
        $parameters = $queryBuilder->getParameters();
        $parameterTypes = $queryBuilder->getParameterTypes();

        $sql = preg_replace_callback(
            '/:([A-Za-z0-9_]+)/',
            static fn(array $match): string => array_key_exists($match[1], $parameters)
                ? ':' . self::SUBQUERY_PARAMETER_PREFIX . $match[1]
                : $match[0],
            $queryBuilder->getSQL()
        );

        $prefixedParameters = [];
        $prefixedParameterTypes = [];
        foreach ($parameters as $name => $value) {
            $prefixedParameters[self::SUBQUERY_PARAMETER_PREFIX . $name] = $value;
            if (array_key_exists($name, $parameterTypes)) {
                $prefixedParameterTypes[self::SUBQUERY_PARAMETER_PREFIX . $name] = $parameterTypes[$name];
            }
        }

        return [$sql, $prefixedParameters, $prefixedParameterTypes];
    }

    /**
     * Removes the ORDER BY parts from the builder and returns them. TYPO3 12 (Doctrine DBAL 3) and
     * TYPO3 13+ (Doctrine DBAL 4) expose them through different methods.
     *
     * @return string[]
     */
    private function takeOrderings(QueryBuilder $queryBuilder): array
    {
        if (method_exists($queryBuilder, 'getOrderBy') && method_exists($queryBuilder, 'resetOrderBy')) {
            $orderings = $queryBuilder->getOrderBy();
            $queryBuilder->resetOrderBy();

            return $orderings;
        }

        if (method_exists($queryBuilder, 'getQueryPart') && method_exists($queryBuilder, 'resetQueryPart')) {
            $orderings = (array)$queryBuilder->getQueryPart('orderBy');
            $queryBuilder->resetQueryPart('orderBy');

            return $orderings;
        }

        throw new \LogicException('The QueryBuilder offers no way to read and reset its orderings.', 1790627277407);
    }

    private function getTableName(Query $query): string
    {
        $source = $query->getSource();
        if (!$source instanceof SelectorInterface) {
            throw new \LogicException('Query source does not implement SelectorInterface.', 1790627277562);
        }

        return $source->getSelectorName();
    }
}
