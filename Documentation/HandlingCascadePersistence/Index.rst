.. _handling_cascade_persistence:

============================
Handling Cascade Persistence
============================

Create relation to existing entity
===================================

It is super easy to create relation to already existing entity. To do so you just need to pass ``uid`` of the related object as a value of property. It works in exactly same way for `One-To-One` and `Many-To-One` relations for whom target entity contains information about one related entity. As on example below - we want to assign ``User`` to ``Department`` assuming that ``User`` can belong to only one ``Department``.

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

.. _handling_cascade_persistence_inline_relations:

Relations stored in the related table
--------------------------------------

Records of relations stored in the related table - TCA ``inline`` with ``foreign_field`` and TCA ``file``
(file references) - keep their parent record in their own row and belong to this parent only. An existing record
of such relation can be passed by ``uid`` (also to update it, see below) only when it already belongs to the same
property of the same record, e.g. when sending the current items again in ``PATCH`` or ``PUT``. Otherwise the
response is ``400 Bad Request``. New items are created in the same request - pass them without ``uid`` using
cascade persistence described below, file references with ``uidLocal`` (see :ref:`handling_file_upload`).

.. code-block:: json

   {
      "steps": [
         1,
         {"uid": 2, "title": "Changed title of existing step"},
         {"title": "New step"}
      ]
   }

The collection sent in the request replaces the current one. Extbase does not delete an item removed from the
collection, it only clears its relation to the parent. To delete removed items, add the Extbase ``Cascade``
attribute to the property. TYPO3 14 reads Extbase annotations only from PHP attributes, not from doc comments.
The form below works in TYPO3 12, 13 and 14 (in TYPO3 14 you can use
``#[\TYPO3\CMS\Extbase\Attribute\ORM\Cascade('remove')]`` instead):

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;
   use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;

   /**
    * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Vendor\Ext\Domain\Model\Step>
    * @T3api\ORM\Cascade("persist")
    */
   #[Cascade(['value' => 'remove'])]
   protected ObjectStorage $steps;

An existing record which can not be found (also a hidden one) returns ``400 Bad Request``. A record passed with
``"uid": 0`` is created as a new one. Value ``0`` clears a single relation; in a collection it returns
``400 Bad Request``.

.. note::
   For a translated record Extbase stores the translated record as the parent of items of such relation. An item
   of the default language shown for the translation (language fallback) is therefore rejected when sent again -
   saving it would move it from the default language record to the translation.

   Do not expose the field which stores the parent (``foreign_field``, e.g. ``recipe`` of a recipe step) in the
   denormalization groups - otherwise a record can be moved to another parent by updating it.

If records of your relations are owned by users and should be checked by their owner, add a rule for it in
``security_post_denormalize`` of the operation, e.g. a method of the model which checks all items, see
:ref:`security`.

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
      ]
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
      ]
   }
