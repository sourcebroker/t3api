.. _filtering_filters_range-filter:

RangeFilter
============

Allows to filter by a value lower than (or equal), greater than (or equal) and between two values.

Syntax: ``?property[<lt|gt|lte|gte|between>]=value``

- ``lt`` - lower than,
- ``lte`` - lower than or equal,
- ``gt`` - greater than,
- ``gte`` - greater than or equal,
- ``between`` - between two values (inclusive) separated with ``..``, e.g. ``?uid[between]=5..100``.

``RangeFilter`` supports two different strategies:

- ``int`` (alternatively ``number`` or ``integer``) - default strategy if not specified. Values passed in filter is casted to integer.
- ``datetime`` - allows to filter results by date time range (value passed in filter is casted to DateTime object before passed to Extbase query). Any format accepted by PHP's ``new \DateTime()`` can be used, e.g. ``2020-05-28``, ``2020-05-28T21:35:55`` or ``2020-05-28T21:35:55+02:00``.

.. important::

   When more than one operator is sent for the same property, conditions are joined with ``OR``, not ``AND``. It means that ``?datetime[gte]=2020-05-01&datetime[lte]=2020-05-31`` returns **all** news (every date is later than first of May **or** earlier than end of May). To get items from a range use ``between``: ``?datetime[between]=2020-05-01..2020-05-31``.

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

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | Get news from between two dates:
     | `https://14.t3api.ddev.site/_api/news/news?datetime[between]=2020-05-28T21:35:55.000..2020-05-29T21:20:00.000 <https://14.t3api.ddev.site/_api/news/news?datetime[between]=2020-05-28T21:35:55.000..2020-05-29T21:20:00.000>`__
     |
   * | Get news that are newer than given date:
     | `https://14.t3api.ddev.site/_api/news/news?datetime[gt]=2020-05-28T21:35:55.000 <https://14.t3api.ddev.site/_api/news/news?datetime[gt]=2020-05-28T21:35:55.000>`__
     |
   * | Get news from 28th May 2020 (whole day):
     | `https://14.t3api.ddev.site/_api/news/news?datetime[between]=2020-05-28T00:00:00..2020-05-28T23:59:59 <https://14.t3api.ddev.site/_api/news/news?datetime[between]=2020-05-28T00:00:00..2020-05-28T23:59:59>`__
     |
