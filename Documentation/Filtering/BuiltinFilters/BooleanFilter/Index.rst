.. include:: ../../Includes.txt

.. _filtering_filters_boolean-filter:

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
