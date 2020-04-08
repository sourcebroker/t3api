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

It is possible to configure filters only for whole resource. That means filters are available for all ``GET`` collection operations within this resource. It is not possible (yet) to configure filters only for specific operations.

There are few build-in filters, as follows:

NumericFilter
==============

Should be used to filter items by numeric fields.

Syntax: ``?property=<int|decimal...>`` or ``?property[]=<int|decimal...>&property[]=<int|decimal...>``.

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
    *     properties={"address.uid", "height"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

SearchFilter
=============

Should be used to filter items by text fields. ``SearchFilter`` supports 3 strategies:

- ``exact`` - Filters items which matches **exactly** search term (``WHERE property = "value"`` in MySQL language). This is strategy used by default if no other is configured.

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
    *
    * @T3api\ApiFilter(
    *     SearchFilter::class,
    *     properties={"firstName", "middleName", "lastName"}
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

- ``partial`` - Filters items which matches **partially** search term (``WHERE property LIKE "%value%"`` in MySQL language).

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
    *
    * @T3api\ApiFilter(
    *     SearchFilter::class,
    *     properties={
    *          "firstName": "partial",
    *          "middleName": "partial",
    *          "lastName": "partial",
    *          "address.street": "partial",
    *     },
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

- ``matchAgainst`` - Filters items which matches search term using **full text search** (``MATCH(property) AGAINST ("value" IN NATURAL LANGUAGE MODE)`` in MySQL language). In it possible to extend query with ``WITH QUERY EXPANSION`` by adding ``withQueryExpansion`` in arguments (`Read more about query expansion <https://dev.mysql.com/doc/refman/5.7/en/fulltext-query-expansion.html>`__)

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
    *
    * @T3api\ApiFilter(
    *     SearchFilter::class,
    *     properties={
    *          "firstName": "matchAgainst",
    *          "middleName": "matchAgainst",
    *          "lastName": "matchAgainst",
    *          "address.street": "matchAgainst",
    *     },
    *     arguments={
    *          "withQueryExpansion": true,
    *     },
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

BooleanFilter
==============

Should be used to filter items by boolean fields.

Syntax: ``?property=<true|false|1|0>``

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
    *
    * @T3api\ApiFilter(
    *     SearchFilter::class,
    *     properties={"isAdmin", "isPublic"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

OrderFilter
============

Allows to change default ordering of collection responses.

Syntax: ``?order[property]=<asc|desc>``

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\OrderFilter;

   /**
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
    *     properties={"firstName", "middleName", "lastName", "address.city"}
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

Configuration of example above means it is possible to order by single field (query string ``?order[firstName]=desc``) or by multiple of them (query string ``?order[firstName]=desc&order[address.city]=asc&order[middleName]=asc``).

It may happen that conflict of names will occur if ``order`` is also the name of property with enabled another filter. Solution in such cases would be a change of parameter name used by ``OrderFilter``. It can be done using argument ``orderParameterName``, as on example below:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\OrderFilter;

   /**
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
    *     properties={"firstName", "middleName", "lastName", "address.city"},
    *     arguments={"orderParameterName": "customOrderParameterName"},
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

Distance filter allows to filter map points points by radius. Map points kept in the database needs to contain latitude and longitude to use this filter.

Configuration for distance filter looks a little bit different than for other build-in filter. Because distance filter is not based on single field it should not contain ``properties`` definition. Instead of that it is needed to specify which model properties contain latitude and longitude in ``arguments``. Moreover, as ``properties`` is not defined, ``parameterName`` is required. Beside default values in ``arguments``, distance filter accepts also:

- ``latProperty`` (``string``) - Name of the property which holds latitude
- ``lngProperty`` (``string``) - name of the property which holds longitude
- ``unit`` (ENUM: "mi", "km"; default "km") - Unit of the radius
- ``radius`` (``float/int``; default ``100``) - Radius to filter in; if ``allowClientRadius`` is set to ``true``, then used as default value.
- ``allowClientRadius`` (``bool``; default ``false``) - Set to ``true`` allow to change the radius from GET parameter.

.. code-block:: php

    use SourceBroker\T3api\Filter\DistanceFilter;

    /**
     * @T3api\ApiFilter(
     *     DistanceFilter::class,
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

By default ``AND`` conjunction is used between all applied filters but there is a ways to change conjunction to ``OR``. Frontend applications often needs single input field which searches multiple fields. To create such filter it is needed to set same ``parameterName`` for multiple fields. Example code below means that request to URL ``/users?search=john`` will return records where any of the fields (``firstName``, ``middleName``, ``lastName`` or ``address.street``) matches searched text. If we would not determine ``parameterName`` in filter arguments this configuration would work as separate filters for every property.

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
    *
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
