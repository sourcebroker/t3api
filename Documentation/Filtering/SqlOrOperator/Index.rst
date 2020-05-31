.. include:: ../../Includes.txt

.. _filtering_sql-or-operator:

SQL "OR" conjunction
=====================

By default ``AND`` conjunction is used between all applied filters but there is a ways to change conjunction to ``OR``. Frontend applications often needs single input field which searches multiple fields. To create such filter it is needed to set same ``parameterName`` for multiple fields. Example code below means that request to URL ``/users?search=john`` will return records where any of the fields (``firstName``, ``middleName``, ``lastName`` or ``address.street``) matches searched text. If we would not determine ``parameterName`` in filter arguments this configuration would work as separate filters for every property.

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
    *     arguments={
    *          "parameterName": "search",
    *     }
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }
