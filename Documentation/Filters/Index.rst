.. include:: ../Includes.txt


.. _filters:

========
Filters
========

Filters gives possibility to customize SQL query used to receive results in ``GET`` collection operation. Information about filters available for operation are returned inside ``hydra:search`` section in response body. To register filter for resource it is needed to add appropriate annotation:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\SearchFilter;
   use SourceBroker\T3api\Filter\NumericFilter;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "get"={
    *              "path"="/users",
    *          },
    *     },
    * )
    * @T3api\ApiFilter(
    *     SearchFilter::class,
    *     properties={
    *          "firstName": "partial",
    *          "middleName": "partial",
    *     },
    * )
    * @T3api\ApiFilter(
    *     NumericFilter::class,
    *     properties={"address.uid"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

For now it is possible to configure filters only for resource. That means filters are available for all ``GET`` collection operations within this resource.
There are few build-in filters, as follows:

NumericFilter
==============

Should be used to filter items by numeric fields.

Syntax: ``?property=<int|decimal...>`` or ``?property[]=<int|decimal...>&property[]=<int|decimal...>``.

Supports SQL ``OR`` conjunction when

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\NumericFilter;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "get"={
    *              "path"="/users",
    *          },
    *     },
    * )
    * @T3api\ApiFilter(
    *     NumericFilter::class,
    *     properties={"address.uid"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

SearchFilter
=============

Should be used to filter items by text fields. ``SearchFilter`` supports 3 strategies:

- ``exact`` - Filters items which matches **exactly** search term (``WHERE property = "value"`` in MySQL language). This is strategy used by default if no other is configured.

@todo add example configuration for ``exact`` strategy

- ``partial`` - Filters items which matches **partially** search term (``WHERE property LIKE "%value%"`` in MySQL language).

@todo add example configuration for ``partial`` strategy

- ``matchAgainst`` - Filters items which matches search term using **full text search** (``MATCH(property) AGAINST ("value" IN NATURAL LANGUAGE MODE)`` in MySQL language). In it possible to extend query with ``WITH QUERY EXPANSION`` by adding ``withQueryExpansion`` in arguments (`Read more about query expansion <https://dev.mysql.com/doc/refman/5.7/en/fulltext-query-expansion.html>`__)

@todo add example configuration for ``matchAgainst`` strategy

BooleanFilter
==============

@todo

OrderFilter
============

@todo

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\OrderFilter;

   /**
    * User
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "get"={
    *              "path"="/users",
    *          },
    *     },
    * )
    *
    * @T3api\ApiFilter(
    *     OrderFilter::class,
    *     properties={"firstName", "middleName", "lastName"}
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

RangeFilter
============

@todo

ContainFilter
==============

@todo

DistanceFilter
===============

As distance calculation depends on two fields (latitude and longitude) there are two conditions to met during
declaration:
1. `properties` should be set to single item with `null` value
2. `parameterName` is required (because `properties` is not declared and can not be used as parameter name)

@todo describe allowed arguments and their purpose:
- `latProperty` (string) - property which holds latitude
- `lngProperty` (string) - property which holds longitude
- `unit` (ENUM: "mi", "km"; default "km") unit of the radius
- `radius` float/int radius to filter by; if `allowClientRadius` is set to true, then used as default value. If
`radius` argument is not set and client did not specify any value then value `100` is used
- `allowClientRadius` (bool; default `false`) - allow

.. code-block:: php

    use SourceBroker\T3api\Filter\DistanceFilter;

    /**
     * @T3api\ApiFilter(
     *     DistanceFilter::class,
     *     properties={null},
     *     arguments={
     *          "parameterName"="position",
     *          "latProperty"="gpsLatitude",
     *          "lngProperty"="gpsLongitude",
     *          "radius"="100",
     *          "unit"="km",
     *     }
     * )
     */
    class Item extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

SQL "IN" operator
==================

When using query params ``?property=<value>`` only items which match exactly such condition are returned. But it is possible to pass multiple values. If you would like to receive all items which ``property`` matches ``value1`` **or** ``value2`` then you can send ``property`` as an array in query string: ``?property[]=<value1>&property[]=<value2>``. From build-in filters ``NumericFilter`` and ``SearchFilter`` are the filters which support ``IN`` operator.

SQL "OR" conjunction
=====================

By default ``AND`` conjunction is used between all applied filters but there is a ways to change conjunction to ``OR``. Frontend applications often needs single input field which searches multiple fields. To create such filter it is needed to configure set same ``parameterName`` for multiple fields. Example code below means that request to URL ``/users?search=john`` will return records where any of the fields (``firstName``, ``middleName``, ``lastName`` or ``address.street``) matches searched text. If we would not determine ``parameterName`` in filter arguments this configuration would work as separate filters for every property.

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\SearchFilter;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "get"={
    *              "path"="/users",
    *          },
    *     },
    * )
    * @T3api\ApiFilter(
    *     SearchFilter::class,
    *     properties={
    *          "firstName": "partial",
    *          "middleName": "partial",
    *          "lastName": "partial",
    *          "address.street": "partial",
    *     },
    *     arguments={
    *          "parameterName": "search",
    *     }
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

Custom filters
===============

@todo
