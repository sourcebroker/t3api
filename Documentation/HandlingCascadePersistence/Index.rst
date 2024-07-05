.. _handling_cascade_persistence:

=======================
Handling Cascade Persistence
=======================

Create relation to existing entity
===================================

It is super easy to create relation to already existing entity. To do so you just need to pass ``uid`` of the related object as a value of property. It works in exactly same way for `One-To-One` and `Many-To-One` relations for whom target entity contains information about one related entity. As on example below - we want to assign ``User`` to ``Department`` assuming that ``User`` can belongs to only one ``Department``.

.. code-block:: json

   {
      "department": 12
   }

In similar way it works also for `One-To-Many` and `Many-To-Many` relations for whom target entity contains a collection of related entities. In such case to create relation it is needed to pass array of identifiers. In example below we create/update news and assign it to categories with specified identifiers.

.. code-block:: json

   {
      "categories": [
         10,
         12,
         13
      ]
   }

Cascade persistence - creating related entity in single request
================================================================

But let's imagine another example. Our application should support creation of the ``Order``. ``Order`` contains collection of ``OrderItem`` entities and ``OrderItem`` contains relation to ``Article`` and ``quantity`` property as a number of ordered articles. To create such structure with identifiers only we would need to create ``n + 1`` requests to the API, where ``n`` is the number of ordered articles. In such case usually much better solution is to create ``OrderItem`` together with ``Order`` entity using cascade persistence technique.

For security reason it is not possible to use cascade persistence by default for all relations. To enable it for specific property it is needed to add annotation ``\SourceBroker\T3api\Annotation\ORM\Cascade`` as on example below.

.. code-block:: php

   class Order extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
   {
       /**
        * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Vendor\Ext\Domain\Model\OrderItem>
        * @SourceBroker\T3api\Annotation\ORM\Cascade("persist")
        */
       protected $items = null;
   }

.. note::
   Extbase has its own ``Cascade`` annotation but unfortunately at current state it does not support ``persist`` functionality. The only supported method is ``remove``. That's why it is needed to use custom annotation.

After enabling cascade persistence for ``Order.items`` property it is possible to send nested objects which will be created in same API request handling. This is an example JSON structure which will create ``Order`` with 3 ``OrderItem`` objects inside. Each of them is related to another ``Article`` entity by passing uid inside of ``article`` property.

.. code-block:: json

   {
      "items": [
        {
            "article": 12,
            "quantity": 5
        },
        {
            "article": 13,
            "quantity": 3
        },
        {
            "article": 14,
            "quantity": 4
        }
      ],
   }

Cascade persistence - updating related entity in single request
================================================================

Let's go further with our example. Let's say that our order can be updated - we can change ``quantity`` of every ``OrderItem`` which is already persisted. Obviously we want to update all ordered items in single request instead of making ``PATCH`` request to ``OrderItem`` endpoint for every changed, ``POST`` request for every added item and ``DELETE`` request for every removed. Doing separate requests is acceptable in some specific cases but most of the time it brings a lot of problems:

- Changes are persisted in multiple database transactions, so if any error occurs during persistence, we would need to find a way to revert those already persisted.
- Objects can not be validated together.
- We need to create ``POST``, ``PATCH`` and ``DELETE`` endpoints to sub-entity most of the time only for this purpose.
- It is not user friendly because processing so many requests surely will take a lot of more time.

Solution for all these problems is cascade update. We can update, add and even remove all nested items in **single request**.

Example payload below is the second request after first from previous example, when we created order using ``POST`` endpoint. Now we want to update order with ``PATCH`` request and do some adjustments in ``items``: Change the quantity of article ``12``, keep the same quantity of article ``13``, remove article ``14`` and add new order item with article ``15``.

- To update ``OrderItem`` we just **include property** ``uid`` for nested objects.
- To remove ``OrderItem`` we just not send it in the collection.
- To add new ``OrderItem`` we just add new nested objects **without** ``uid`` property.

.. code-block:: json

   {
      "items": [
        {
            "uid": 1,
            "article": 12,
            "quantity": 8
        },
        {
            "uid": 2,
            "article": 13,
            "quantity": 3
        },
        {
            "article": 15,
            "quantity": 9
        }
      ],
   }
