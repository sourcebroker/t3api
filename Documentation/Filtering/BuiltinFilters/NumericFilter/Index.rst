.. include:: ../../Includes.txt

.. _filtering_filters_numeric-filter:

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
    *     properties={"address.number", "height"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }
