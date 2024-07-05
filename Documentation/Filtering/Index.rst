.. _filtering:

=========
Filtering
=========

Filters gives possibility to customize SQL query used to receive results in ``GET`` collection operation. Information about filters available for operation are returned inside ``hydra:search`` section in response body. To register filter for resource it is needed to add appropriate annotation:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use SourceBroker\T3api\Filter\SearchFilter;
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
    *     SearchFilter::class,
    *     properties={
    *          "firstName": "partial",
    *          "middleName": "partial",
    *     },
    * )
    * @T3api\ApiFilter(
    *     NumericFilter::class,
    *     properties={"address.uid"},
    * )
    */
   class User extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
   {
   }

It is possible to configure filters only for whole resource. That means filters are available for all ``GET`` collection operations within this resource. It is not possible (yet) to configure filters only for specific operations.
There are few build-in filters. See the next pages.

.. toctree::
   :maxdepth: 3
   :hidden:

   BuiltinFilters/Index
   CustomFilters/Index
   SqlInOperator/Index
   SqlOrOperator/Index

