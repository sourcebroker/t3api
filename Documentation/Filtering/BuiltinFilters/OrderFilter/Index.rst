.. include:: ../../Includes.txt

.. _filtering_filters_order-filter:

OrderFilter
============

Allows to change default ordering of collection responses.

Syntax: ``?order[property]=<asc|desc>``

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\OrderFilter;

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
    *     OrderFilter::class,
    *     properties={"firstName", "middleName", "lastName", "address.city"}
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

Configuration of example above means it is possible to order by single field (query string ``?order[firstName]=desc``) or by multiple of them (query string ``?order[firstName]=desc&order[address.city]=asc&order[middleName]=asc``).

It may happen that conflict of names will occur if ``order`` is also the name of property with enabled another filter. Solution in such cases would be a change of parameter name used by ``OrderFilter``. It can be done using argument ``orderParameterName``, as on example below:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\OrderFilter;

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
    *     OrderFilter::class,
    *     properties={"firstName", "middleName", "lastName", "address.city"},
    *     arguments={"orderParameterName": "customOrderParameterName"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }


.. admonition:: Real examples. Install `T3API Demo <https://github.com/sourcebroker/t3apidemo>`__  and try those links below.

   * | Get list of news sorted by titles ascending:
     | https://t3api-demo.ddev.site/_api/news/news?order[title]=asc
     |
   * | Get list of news sorted by date descending and then by title ascending:
     | https://t3api-demo.ddev.site/_api/news/news?order[datetime]=desc&order[title]=asc

