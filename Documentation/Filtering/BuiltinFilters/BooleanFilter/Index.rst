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

.. admonition:: Real examples. Install `T3API Demo <https://github.com/sourcebroker/t3apidemo>`__  and try those links below.

   * | Get list of news which are "Top News":
     | https://t3api-demo.ddev.site/_api/news/news?istopnews=true
     |
   * | Get list of news which are "Top News" and sort by title
     | https://t3api-demo.ddev.site/_api/news/news?istopnews=true&order[title]=asc
