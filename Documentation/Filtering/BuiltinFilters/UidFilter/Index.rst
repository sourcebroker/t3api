.. _filtering_filters_uid-filter:

UidFilter
=========

Should be used to filter items by ``uid`` property. In fact it only extends ``NumericFilter`` but beside adding new condition into SQL's ``WHERE`` clause it also modifies Extbase's Query Settings to make it possible to find translated records by passing default language record ``uid`` value. Modification in Query Settings are exactly the same as modifications which are applied when ``\TYPO3\CMS\Extbase\Persistence\Repository::findByUid`` is called.

An example - if default record ``uid`` is ``8`` and ``uid`` for translated record is ``10`` and you are doing request for translation (:ref:`see how multilanguage works in t3api <multilanguage>`) then you could add a param ``uid`` with value 8 (``?uid=8`` or ``?uid[]=8``). If you would use ``NumericFilter`` in same example, then it would be needed to set ``uid`` param to ``10`` (otherwise record would not be included in response).

.. important::

   If your site have more than one language it is really recommended to use ``UidFilter`` instead of ``NumericFilter`` on the ``uid`` field.

Syntax: ``?property=<int>`` or ``?property[]=<int>&property[]=<int>``.

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\UidFilter;

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
    *     UidFilter::class,
    *     properties={"uid"},
    * )
    */
   class News extends \GeorgRinger\News\Domain\Model\News
   {
   }


.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | Get news with uid 1, 2, 3 in German - translated records are found by uid of default language record (DE translations have uids 8 and 10):
     | `https://14.t3api.ddev.site/de/_api/news/news?uid[]=1&uid[]=2&uid[]=3 <https://14.t3api.ddev.site/de/_api/news/news?uid[]=1&uid[]=2&uid[]=3>`__
