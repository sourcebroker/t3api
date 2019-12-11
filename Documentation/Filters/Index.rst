.. include:: ../Includes.txt


.. _filters:

========
Filters
========

@todo
@todo describe ``hydra:search``

SearchFilter
=============

@todo

It is possible to filter multiple fields by single parameter. Example code below means that request to URL
``/users?search=john`` will return records where any of the fields (``firstName``, ``middleName``, ``lastName``)
matches searched text.

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
    *     },
    *     arguments={
    *          "parameterName": "search",
    *     }
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

BooleanFilter
==============

@todo

NumericFilter
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

Custom filters
===============

@todo
