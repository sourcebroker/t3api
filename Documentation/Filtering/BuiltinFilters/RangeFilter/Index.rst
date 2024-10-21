.. _filtering_filters_range-filter:

RangeFilter
============

Allows to filter by a value lower than (or equal), greater than (or equal) and between two values.

Syntax: ``?property[<lt|gt|lte|gte|between>]=value``

``RangeFilter`` supports two different strategies:

- ``int`` (alternatively ``number`` or ``integer``) - default strategy if not specified. Values passed in filter is casted to integer.
- ``datetime`` - allows to filter results by date time range (value passed in filter is casted to DateTime object before passed to Extbase query).

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\RangeFilter;

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
    *     RangeFilter::class,
    *     properties={
    *          "datetime": "datetime",
    *          "uid": "int",
    *     },
    * )
    */
    class News extends \GeorgRinger\News\Domain\Model\News
   {
   }

.. admonition:: Real examples. Run "ddev restart && ddev ci 13" and try those links below.

   * | Get news from between two dates:
     | `https://13.t3api.ddev.site/_api/news/news?datetime[between]=2020-05-28T21:35:55.000..2020-05-29T21:20:00.000 <https://13.t3api.ddev.site/_api/news/news?datetime[between]=2020-05-28T21:35:55.000..2020-05-29T21:20:00.000>`__
     |
   * | Get news that are older than:
     | `https://13.t3api.ddev.site/_api/news/news?datetime[gt]=2020-05-28T21:35:55.000 <https://13.t3api.ddev.site/_api/news/news?datetime[gt]=2020-05-28T21:35:55.000>`__
     |
   * | Get news with uid between 5 and 100 (Note! It will return newses from all languages. Use UidFilter instead if you want to get language dependent newses):
     | `https://13.t3api.ddev.site/_api/news/news?uid[between]=5..100 <https://13.t3api.ddev.site/_api/news/news?uid[between]=5..100>`__
     |
