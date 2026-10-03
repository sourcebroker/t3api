.. _filtering_filters_contain-filter:

ContainFilter
==============

Should be used to filter items by fields which keep a **comma separated list of values** in a single database column (e.g. ``fe_users.usergroup``, ``pages.fe_group`` or any custom ``select`` field with ``renderType=selectCheckBox`` / ``selectMultipleSideBySide`` which is stored without MM table).

Under the hood ``ContainFilter`` uses MySQL's ``FIND_IN_SET()`` function, so request ``?usergroup=2`` matches records where ``usergroup`` column is e.g. ``2``, ``1,2`` or ``2,5,7`` but **not** ``12`` or ``20`` (as it would happen with ``LIKE "%2%"``).

Syntax: ``?property=<value>`` or ``?property[]=<value1>&property[]=<value2>``.

When multiple values are passed then items which contain **any** of the values are returned (``FIND_IN_SET("value1", property) > 0 OR FIND_IN_SET("value2", property) > 0``).

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\ContainFilter;

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
    *     ContainFilter::class,
    *     properties={"usergroup"}
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

With configuration above:

- ``/users?usergroup=2`` - returns users which belong to user group with uid ``2``.
- ``/users?usergroup[]=2&usergroup[]=5`` - returns users which belong to user group ``2`` **or** ``5``.

Nested properties (e.g. ``address.tags``) are supported as well - required joins are added automatically.

.. note::

   ``ContainFilter`` is not meant for relations stored in MM tables (e.g. news categories). For such relations use
   :ref:`SearchFilter <filtering_filters_search-filter>` or :ref:`NumericFilter <filtering_filters_numeric-filter>`
   on a nested property, e.g. ``categories.uid``.
