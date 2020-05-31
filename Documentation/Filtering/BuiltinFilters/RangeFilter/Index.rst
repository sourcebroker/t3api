.. include:: ../../Includes.txt

.. _filtering_filters_range-filter:

RangeFilter
============

Allows to filter by a value lower than (or equal), greater than (or equal) and between two values.

Syntax: ``?property[<lt|gt|lte|gte|between>]=value``

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\RangeFilter;

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
    *     RangeFilter::class,
    *     properties={"uid"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

``RangeFilter`` supports two different strategies:

- ``int`` (alternatively ``number`` or ``integer``) - default strategy if not specified. Values passed in filter is casted to integer.
- ``datetime`` - allows to filter results by date time range (value passed in filter is casted to DateTime object before passed to Extbase query).

.. code-block:: php

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
    *     RangeFilter::class,
    *     properties={
    *          "starttime": "datetime",
    *          "endtime": "datetime",
    *          "uid": "int",
    *     },
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }
