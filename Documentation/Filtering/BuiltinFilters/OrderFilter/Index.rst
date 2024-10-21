.. _filtering_filters_order-filter:

OrderFilter
============

Allows to change default ordering of collection responses.

Syntax: ``?order[property]=<asc|desc>``

It is possible to order by single field (query string :uri:`?order[title]=asc`) or by multiple of them (query string :uri:`?order[title]=asc&order[datetime]=desc`).

It may happen that conflict of names will occur if ``order`` is also the name of property with enabled another filter. Solution in such cases would be a change of parameter name used by ``OrderFilter``. It can be done using argument ``orderParameterName``, as on example below:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\OrderFilter;

   /**
    * @T3api\ApiResource (
    *     collectionOperations={
    *          "get"={
    *              "path"="/news/news",
    *          },
    *     },
    * )
    *
    * @T3api\ApiFilter(
    *     OrderFilter::class,
    *     properties={"title","datetime"}
    *     arguments={"orderParameterName": "myOrderParameterName"},
    * )
    */
    class News extends \GeorgRinger\News\Domain\Model\News
   {
   }


.. admonition:: Real examples. Run "ddev restart && ddev ci 13" and try those links below.

   * | Get list of news sorted by titles ascending:
     | `https://13.t3api.ddev.site/_api/news/news?order[title]=asc <https://13.t3api.ddev.site/_api/news/news?order[title]=asc>`__
     |
   * | Get list of news sorted by date descending and then by title ascending:
     | `https://13.t3api.ddev.site/_api/news/news?order[datetime]=desc&order[title]=asc <https://13.t3api.ddev.site/_api/news/news?order[datetime]=desc&order[title]=asc>`__
