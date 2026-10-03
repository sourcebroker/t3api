.. _filtering_filters_numeric-filter:

NumericFilter
==============

Should be used to filter items by integer fields (e.g. ``pid``, relation uids, counters).

Syntax: ``?property=<int>`` or ``?property[]=<int>&property[]=<int>``.

.. note::

   Values are casted to integer, so ``?height=1.85`` is the same as ``?height=1``. To filter decimal fields use
   :ref:`RangeFilter <filtering_filters_range-filter>` or a :ref:`custom filter <filtering_custom-filters>`.

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
    *     properties={"address.number", "age"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

.. important::

   If you want to filter by ``uid`` on a site with more than one language use
   :ref:`UidFilter <filtering_filters_uid-filter>` instead of ``NumericFilter``.

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   News resource of testing instance has ``NumericFilter`` configured for ``pid`` property: ``@T3api\ApiFilter(NumericFilter::class, properties={"pid"})``.

   * | Get list of news stored on page (folder) with uid 6:
     | `https://14.t3api.ddev.site/_api/news/news?pid=6 <https://14.t3api.ddev.site/_api/news/news?pid=6>`__
     |
   * | Get list of news stored on page 3 or 6 (see :ref:`SQL "IN" operator <filtering_sql-in-operator>`):
     | `https://14.t3api.ddev.site/_api/news/news?pid[]=3&pid[]=6 <https://14.t3api.ddev.site/_api/news/news?pid[]=3&pid[]=6>`__
     |
