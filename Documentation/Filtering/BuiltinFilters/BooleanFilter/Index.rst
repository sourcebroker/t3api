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
    *              "method"="GET",
    *              "path"="/news/news",
    *          },
    *     },
    * )
    *
    * @T3api\ApiFilter(
    *     BooleanFilter::class,
    *     properties={"istopnews"}
    * )
    */
   class News extends \GeorgRinger\News\Domain\Model\News
   {
   }

.. admonition:: Real examples. Run "ddev restart && ddev ci 13" and try those links below.

   * | Get list of news which are "Top News":
     | https://13.t3api.ddev.site/_api/news/news?istopnews=true
     |
   * | Get list of news which are "Top News" and sort by title
     | `https://13.t3api.ddev.site/_api/news/news?istopnews=true&order[title]=asc <https://13.t3api.ddev.site/_api/news/news?istopnews=true&order[title]=asc>`__
