.. _filtering_custom-filters:

==============
Custom filters
==============

It is super easy to create custom filters which will match your specific requirements.
Custom filters have to implement interface ``\SourceBroker\T3api\Filter\FilterInterface`` and contain one public method ``filterProperty``.
Method ``filterProperty`` accepts 4 arguments:

- $property (``string``) - Name of the property to filter by.
- $values (``mixed``) - Values passed in request.
- $query (``TYPO3\CMS\Extbase\Persistence\QueryInterface``) - Instance of Extbase's query.
- $apiFilter (``SourceBroker\T3api\Domain\Model\ApiFilter``) - Instance of T3api's filter.

.. important::
    Method ``filterProperty`` has to return ``null`` or instance of ``TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface``.
    If it returns null it won't affect the final result at all. If it returns ``ConstraintInterface`` returned constraint will be added to final query.

.. note::
    Keep in mind that second argument (``$values``) is mixed type. Depending on requested URL it can be array or string.
    If requests's query string contains ``property=hello`` then ``$values`` will be a string ``hello``.
    If requests's query string contains ``property[]=hello`` then ``$values`` will be a array with one string inside (``hello``).
    Consider casting ``$values`` to array inside your filter to avoid bugs (``$values = (array)$values;``).

Example implementation of custom filter may looks as follows:

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Extension\Filter;

   use SourceBroker\T3api\Domain\Model\ApiFilter;
   use SourceBroker\T3api\Filter\FilterInterface;
   use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
   use TYPO3\CMS\Extbase\Persistence\QueryInterface;

   class MyCustomFilter implements FilterInterface
   {
       public function filterProperty(string $property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
       {
           return $query->equals($property, $values);
       }
   }

It may be useful, but not required, to extend class ``\SourceBroker\T3api\Filter\AbstractFilter`` which will give you bunch of methods inside your filter. An example of such method may be ``addJoinsForNestedProperty`` which may be really useful to handle more complex filters, especially when you need to implement something which is out of reach Extbase's query builder. Check code inside ``\SourceBroker\T3api\Filter\ContainFilter`` and ``\SourceBroker\T3api\Filter\SearchFilter`` for example usages.

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Extension\Filter;

   use SourceBroker\T3api\Domain\Model\ApiFilter;
   use SourceBroker\T3api\Filter\AbstractFilter;
   use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
   use TYPO3\CMS\Extbase\Persistence\QueryInterface;

   class MyCustomFilter extends AbstractFilter
   {
       public function filterProperty(string $property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
       {
           // ...
       }
   }

.. note::
    Instance of your custom filter is created with ``GeneralUtility::makeInstance()``, so constructor injection works if the filter is a public service (``public: true`` in ``Configuration/Services.yaml``). Filters are shared instances - do not keep request specific state in them, or reset it after use.

.. _filtering_custom-filters_query-modifiers:

Query modifiers
===============

A constraint returned from ``filterProperty`` is sometimes not enough — for example when the filter needs to apply a custom ``ORDER BY`` which has to take the complete query into account. For such cases a custom filter can additionally implement ``\SourceBroker\T3api\Filter\QueryModifierInterface``:

.. code-block:: php

   interface QueryModifierInterface
   {
       public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void;
   }

Method ``modifyQuery`` accepts 3 arguments:

- $query (``TYPO3\CMS\Extbase\Persistence\QueryInterface``) - Instance of Extbase's query, already carrying the combined constraints of all filters.
- $apiFilter (``SourceBroker\T3api\Domain\Model\ApiFilter``) - The filter declaration being applied.
- $operation (``SourceBroker\T3api\Domain\Model\CollectionOperation``) - The collection operation being served, so the filter can act per endpoint (path, resource, attributes).

After the constraints of all filters have been combined and applied to the query, t3api calls ``modifyQuery`` on every filter implementing this interface. Query modifiers only ever run for collection ``GET`` operations — the same scope in which filters apply at all.

The filters run in the same order as ``filterProperty`` — the order of their parameters in the query string as normalized by Symfony's ``Request::getQueryString()``, which sorts the parameters **by name** with PHP's ``ksort()`` — in byte order: digits first, then uppercase letters, ``_`` and lowercase letters (``Zeta`` comes before ``alpha``). The position of a parameter in the URL does not matter: ``?search=a&titleSearch=b`` and ``?titleSearch=b&search=a`` are processed identically (``search`` first). Only the order of nested keys is kept as requested, e.g. ``?order[title]=asc&order[uid]=desc``.

Prefer Extbase's query API
--------------------------

Whenever Extbase's query API can express the change, use it — the query then keeps going through Extbase's regular path (``DISTINCT`` for to-many relations, the count query, Extbase's persistence events). A typical case for a modifier instead of a plain ``filterProperty`` constraint is a change which has to take the constraints of all other filters into account, e.g. always including featured records, whatever the other filters match:

.. code-block:: php

   public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void
   {
       $featured = $query->equals('featured', true);
       $constraint = $query->getConstraint();

       $query->matching($constraint === null ? $featured : $query->logicalOr($constraint, $featured));
   }

Shared QueryBuilder statement
-----------------------------

Only when Extbase's query API cannot express the change, a modifier works on the shared Doctrine QueryBuilder statement returned by ``$this->getOrCreateStatementQueryBuilder($query)`` (available when extending ``\SourceBroker\T3api\Filter\AbstractFilter``). Typical use cases:

- ordering by a list resolved outside of the database — a ranking of a search engine or a recommendation service (``ORDER BY FIELD(...)``, ``CASE``), see :ref:`AbstractOrderedUidsFilter <filtering_ordered-uids-filter>`,
- conditions using SQL functions (``LENGTH()``, ``FIND_IN_SET()``, JSON functions, ...),
- ordering by a computed value, e.g. a geographical distance or the score of ``MATCH ... AGAINST``,
- conditions on aggregated subqueries (e.g. ``EXISTS`` with ``GROUP BY ... HAVING COUNT(...)``).

Example of a filter keeping records whose title is not longer than the requested number of characters (``?maxTitleLength=10``):

.. code-block:: php

   use TYPO3\CMS\Core\Database\Connection;

   class MaxTitleLengthFilter extends AbstractFilter implements QueryModifierInterface
   {
       private int $maxTitleLength = 0;

       public function filterProperty(string $property, $values, QueryInterface $query, ApiFilter $apiFilter): ?ConstraintInterface
       {
           $this->maxTitleLength = (int)(is_array($values) ? reset($values) : $values);

           return null;
       }

       public function modifyQuery(QueryInterface $query, ApiFilter $apiFilter, CollectionOperation $operation): void
       {
           $maxTitleLength = $this->maxTitleLength;
           $this->maxTitleLength = 0;

           if ($maxTitleLength <= 0 || !$query instanceof Query) {
               return;
           }

           $queryBuilder = $this->getOrCreateStatementQueryBuilder($query);
           $queryBuilder->andWhere(
               'LENGTH(' . $queryBuilder->quoteIdentifier($this->getTableName($query) . '.title') . ') <= '
               . $queryBuilder->createNamedParameter($maxTitleLength, Connection::PARAM_INT)
           );
       }
   }

.. warning::
    Never concatenate a value coming from the request into the SQL of the statement — bind it with
    ``$queryBuilder->createNamedParameter()`` as above, and quote table and column names with
    ``$queryBuilder->quoteIdentifier()``. Filters are singletons, so a value passed from
    ``filterProperty`` to ``modifyQuery`` through a property should be reset after use (keyed by
    ``spl_object_id($apiFilter)`` if one filter class is declared under several parameter names).

TYPO3's ``QueryBuilder::addOrderBy()`` quotes its argument as a column name, so an ``ORDER BY`` expression goes to the underlying Doctrine builder (marked ``@internal`` by TYPO3 core, but the only way to add an expression):

.. code-block:: php

   $queryBuilder = $this->getOrCreateStatementQueryBuilder($query);
   $queryBuilder->getConcreteQueryBuilder()->addOrderBy(
       'CASE WHEN ' . $queryBuilder->quoteIdentifier($this->getTableName($query) . '.featured')
       . ' = 1 THEN 0 ELSE 1 END'
   );

The first call of ``getOrCreateStatementQueryBuilder()`` converts the fully constrained query into a QueryBuilder statement, every following call — also from other modifiers — returns the same builder, so the changes of all modifiers compose:

- orderings added by modifiers follow each other in the order in which the modifiers run and precede the orderings requested through ``OrderFilter`` (``order[...]``), which are moved to the end of the ``ORDER BY`` clause,
- constraints on to-many relations (e.g. a ``SearchFilter`` on ``tags.title``), which Extbase resolves with a ``JOIN`` and ``DISTINCT``, are wrapped into ``uid IN (...)``, so that every record is still returned and counted once — also in ``totalItems``.

.. important::
    Do not call ``$query->statement(...)`` yourself: Extbase then executes only that statement, so
    QOM-level changes made by later modifiers are ignored and a later ``statement()`` call
    overwrites an earlier one (last one wins). For the same reason, changes made through Extbase's
    query API in ``modifyQuery`` are ignored once any modifier created the statement — apply them
    in ``filterProperty`` instead. This also applies to listeners of Extbase's
    ``ModifyQueryBeforeFetchingObjectDataEvent`` (and, since TYPO3 v14,
    ``ModifyQueryBeforeFetchingObjectCountEvent``): when a query modifier created the statement,
    changes such a listener makes through Extbase's query API have no effect on that request, and
    a listener replacing the query with ``setQuery()`` drops the statement together with the
    changes of all query modifiers.

A ready-made base class using this seam is :ref:`AbstractOrderedUidsFilter <filtering_ordered-uids-filter>`, which returns a collection in the order of an externally resolved UID list (e.g. search results ordered by relevance).

Registration
============

To use your new custom filter is it just needed to pass it as first parameter into ``@ApiFilter`` annotation:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use Vendor\Extension\Filter\MyCustomFilter;

   /**
    * @T3api\ApiFilter(
    *     MyCustomFilter::class,
    *     properties={"username"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }
