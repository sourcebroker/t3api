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

The ordering plays well with the rest of t3api: pagination (``limit``/``offset``) is applied on top of it and ``totalItems`` is still calculated from the unpaginated query.

Several such filters on one resource compose as well: each filter's ``uid IN (...)`` constraint applies (the collection is the intersection of all matches), the ranking of the first filter present in the request's query string becomes the primary ordering and the following ones are appended as tie-breakers.

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
