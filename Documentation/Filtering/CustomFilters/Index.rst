.. _filtering_custom-filters:

==============
Custom filters
==============

It is super easy to create custom filters which will match your specific requirements.
Custom filters has to implement interface ``\SourceBroker\T3api\Filter\FilterInterface`` and contain one public method ``filterProperty``.
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
    Instance of your custom filter will be created using Extbase's ObjectManager, so you can inject into it any other services if you need them.

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

After the constraints of all filters have been combined and applied to the query, t3api calls ``modifyQuery`` on every filter implementing this interface. The filters run in the same request-driven order as ``filterProperty``. Query modifiers only ever run for collection ``GET`` operations — the same scope in which filters apply at all.

.. important::
    Modifications done through Extbase's query API compose across multiple modifiers. A modifier
    which replaces the query with a Doctrine QueryBuilder statement (``$query->statement(...)``)
    does not: Extbase then executes only the statement, so QOM-level changes made by later
    modifiers are ignored, and a later ``statement()`` call overwrites an earlier one (last one
    wins). A statement-based modifier should check ``$query->getStatement()`` first and mutate
    the QueryBuilder already present instead of reconverting — see
    ``\SourceBroker\T3api\Filter\AbstractOrderedUidsFilter`` for a reference implementation.

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
