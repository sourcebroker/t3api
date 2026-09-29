.. _filtering_ordered-uids-filter:

===================
Ordered UIDs filter
===================

``\SourceBroker\T3api\Filter\AbstractOrderedUidsFilter`` is an abstract base class for filters which resolve the filter value to an ordered list of record UIDs and want the collection returned in exactly that order. Typical use cases:

- relevance-ranked results from an external search engine (Elasticsearch, Algolia, Solr, ...),
- recommendations computed by an external service,
- manually curated lists.

A concrete filter implements a single method — ``resolveOrderedUids()``. The base class then does the rest:

- it constrains the collection query to the resolved UIDs (``uid IN (...)``),
- as a :ref:`query modifier <filtering_custom-filters_query-modifiers>` it applies the ordering as ``ORDER BY FIELD(uid, ...)`` after the constraints of all filters have been combined — an ordering which Extbase's query API cannot express on its own.

The ordering plays well with the rest of t3api: pagination (``limit``/``offset``) is applied on top of it and ``totalItems`` is still calculated from the unpaginated query. Other filters, including those constraining to-many relations (e.g. ``tags.title``), can be combined with it — every record is returned and counted once.

Several such filters on one resource compose as well: each filter's ``uid IN (...)`` constraint applies (the collection is the intersection of all matches). Their rankings follow the order in which the query modifiers run, which is the **sorted order of the parameter names**, not their position in the URL (see :ref:`query modifiers <filtering_custom-filters_query-modifiers>`): with ``?titleSearch=b&search=a`` the ranking of ``search`` is the primary ordering. As the rankings are applied to the intersection of all matches, where every record has a distinct position in the first ranking, the following rankings do not change the result in practice.

Return value contract of ``resolveOrderedUids()``:

- ``null`` - no lookup was performed; the filter is inactive and does not affect the result at all,
- ``[]`` (empty array) - the lookup was performed but nothing matched; the collection result will be empty,
- ``[12, 5, 8]`` - the matching UIDs, in the order in which they should be returned.

.. important::
    The generated ``ORDER BY FIELD()`` clause is specific to MySQL and MariaDB. If you run TYPO3 on another DBMS, you need a different solution.

Example implementation of a filter backed by an external search engine:

.. code-block:: php

   declare(strict_types=1);
   namespace Vendor\Extension\Filter;

   use SourceBroker\T3api\Domain\Model\ApiFilter;
   use SourceBroker\T3api\Filter\AbstractOrderedUidsFilter;
   use Vendor\Extension\Service\SearchEngineClient;

   class RelevanceSearchFilter extends AbstractOrderedUidsFilter
   {
       public function __construct(private readonly SearchEngineClient $searchEngineClient)
       {
       }

       protected function resolveOrderedUids($values, ApiFilter $apiFilter): ?array
       {
           $phrase = trim((string)(is_array($values) ? reset($values) : $values));

           if ($phrase === '') {
               return null;
           }

           // Returns the matching record UIDs ordered by relevance score.
           return $this->searchEngineClient->searchUids($phrase);
       }
   }

Registration works the same as for any other filter:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use Vendor\Extension\Filter\RelevanceSearchFilter;

   /**
    * @T3api\ApiFilter(
    *     RelevanceSearchFilter::class,
    *     arguments={"parameterName"="search"}
    * )
    */
   class Recipe extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
   {
   }

A request like ``/api/recipes?search=pasta`` then returns the matching records in the order delivered by the search engine, with pagination and ``totalItems`` working as usual.

Ranking and ``OrderFilter``
===========================

By default the ranking is the primary ordering and orderings requested through :ref:`OrderFilter <filtering_filters_order-filter>` (``order[...]``) only break its ties — ``?search=pasta&order[title]=asc`` returns the records in the order of the search engine. The ``rankingPrecedence`` argument turns this around per filter declaration:

- ``beforeOrderFilter`` (default) — the ranking precedes ``order[...]``,
- ``afterOrderFilter`` — ``order[...]`` is the primary ordering and the ranking breaks its ties, e.g. ``?search=pasta&order[rating]=desc`` returns the best rated matches first, records with the same rating in the order of the search engine. Without ``order[...]`` in the request the ranking still applies.

.. code-block:: php

   /**
    * @T3api\ApiFilter(
    *     RelevanceSearchFilter::class,
    *     arguments={"parameterName"="search", "rankingPrecedence"="afterOrderFilter"}
    * )
    */
