.. include:: ../../Includes.txt

.. _filtering_filters_search-filter:

SearchFilter
============

Should be used to filter items by text fields. ``SearchFilter`` supports 3 strategies:

- ``exact`` - Filters items which matches **exactly** search term (``WHERE property = "value"`` in MySQL language). This is strategy used by default if no other is configured.

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
    *     properties={"firstName", "middleName", "lastName"}
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

- ``partial`` - Filters items which matches **partially** search term (``WHERE property LIKE "%value%"`` in MySQL language).

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
    *     properties={
    *          "firstName": "partial",
    *          "middleName": "partial",
    *          "lastName": "partial",
    *          "address.street": "partial",
    *     },
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

- ``matchAgainst`` - Filters items which matches search term using **full text search** (``MATCH(property) AGAINST ("value" IN NATURAL LANGUAGE MODE)`` in MySQL language). In it possible to extend query with ``WITH QUERY EXPANSION`` by adding ``withQueryExpansion`` in arguments (`Read more about query expansion <https://dev.mysql.com/doc/refman/5.7/en/fulltext-query-expansion.html>`__)

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
    *     properties={
    *          "firstName": "matchAgainst",
    *          "middleName": "matchAgainst",
    *          "lastName": "matchAgainst",
    *          "address.street": "matchAgainst",
    *     },
    *     arguments={
    *          "withQueryExpansion": true,
    *     },
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

