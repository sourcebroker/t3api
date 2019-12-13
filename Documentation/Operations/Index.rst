.. include:: ../Includes.txt


.. _operations:

===========
Operations
===========

T3api is based on operations. Operation in fact is an API endpoint, which allows to access resources.
There are two types of operations: collection and item.
As you can guess, collection operation returns multiple items of resource and item operation returns single item
(fetched by specified id).

Configuring operations
========================

To configure operation for resource you need to pass ``collectionOperations`` or ``itemOperations`` parameters into
``ApiResource`` annotation as on example below. Don't bother about the key of the operation for now. In our example
we use ``get``, but you could use there any other string. The important part is the ``path``. This is the URL path
for the endpoint, prefixed by the ``basePath`` from route enhancer (see more on :ref:`route-enhancer`).

Path of the item operation needs to contain ``{id}`` parameter. This parameter is replaced by ``uid`` of the entity
when fetching single item.

.. important::
    Path to API endpoints is prefixed by the default language base path. For example: If your default language base
    path is ``en`` then endpoint URL will be: ``/en/_api/*``.

.. code-block:: php

    use SourceBroker\T3api\Annotation\ApiResource;

    /**
     * @ApiResource(
     *     collectionOperations={
     *          "get"={
     *              "path"="/items",
     *          },
     *     },
     *     itemOperations={
     *          "get"={
     *              "path"="/items/{id}",
     *          }
     *     },
     * )
     */
    class Item extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
    }

.. note::
    You can determine multiple operations of each type for every property, but keep in mind that first collection
    operation and first item operation are treated as the main operations. It means that:

    - The path of the first collection operation is used in the main endpoint (@todo ref to main endpoint)
    - The path of the first item operation is used to define resource IRI (``@id`` property).

Supported operation methods
=============================

T3api supports all REST methods

+--------------+-----------------+-------------------+--------------------------------------+
| HTTP Method  | Operation type  | Example URL       | Purpose                              |
+==============+=================+===================+======================================+
| **GET**      | Collection      | \/resource        | Reading collection of the items      |
+--------------+-----------------+-------------------+--------------------------------------+
| **GET**      | Item            | \/resource\/{id}  | Reading single item                  |
+--------------+-----------------+-------------------+--------------------------------------+
| **POST**     | Collection      | \/resource        | Creating new item                    |
+--------------+-----------------+-------------------+--------------------------------------+
| **PATCH**    | Item            | \/resource\/{id}  | Updating the item                    |
+--------------+-----------------+-------------------+--------------------------------------+
| **PUT**      | Item            | \/resource\/{id}  | Replacing the item                   |
+--------------+-----------------+-------------------+--------------------------------------+
| **DELETE**   | Item            | \/resource\/{id}  | Deleting the item                    |
+--------------+-----------------+-------------------+--------------------------------------+

.. important::
    Notice that ``POST`` operation is a type of collection operations

Response of GET collection operation
========================================

As mentioned in :ref:`getting-started` T3api uses `Hydra Core Vocabulary <http://www.hydra-cg.com/>`__. That's why collection
response of GET method is enriched by some additional properties:

- ``hydra:member`` - contains array of matched entities.
- ``hydra:totalItems`` - contains number of all items.
- ``hydra:view`` - contains data useful for pagination (see more on :ref:`pagination`).
- ``hydra:search`` - contains data useful for filtering (see more on :ref:`filters`).

Here is an example of basic response for collection operation.

.. code-block:: json

    {
      "hydra:member": [
        {
          "@id": "/_api/news/news/1",
          "uid": 1,
          "title": "Lorem ipsum dolor sit amet enim",
          "teaser": "Pellentesque facilisis. Nulla imperdiet sit amet magna.",
          "datetime": "2019-08-30T07:30:00+00:00"
        },
        {
          "@id": "/_api/news/news/2",
          "uid": 2,
          "title": "Lorem ipsum dolor sit amet enim",
          "teaser": "Aliquam erat ac ipsum. Integer aliquam purus",
          "datetime": "2019-08-30T07:30:00+00:00"
        }
      ],
      "hydra:totalItems": 2,
      "hydra:view": {
        "hydra:first": "/_api/items?page=1",
        "hydra:last": "/_api/items?page=10",
        "hydra:prev": "/_api/items?page=3",
        "hydra:next": "/_api/items?page=5",
        "hydra:pages": [
            "/_api/items?page=1",
            "/_api/items?page=2",
            "/_api/items?page=3",
            "/_api/items?page=4",
            "/_api/items?page=5",
            "/_api/items?page=6",
            "/_api/items?page=7",
            "/_api/items?page=8",
            "/_api/items?page=9",
            "/_api/items?page=10"
        ]
      }
    }

Item operation response
========================

In response of all item operations only object is received. Response does not contain any additional attributes
because they are useless in single item operation context.

.. code-block:: json

    {
      "@id": "/_api/news/news/1",
      "uid": 1,
      "title": "Lorem ipsum dolor sit amet enim",
      "teaser": "Pellentesque facilisis. Nulla imperdiet sit amet magna.",
      "datetime": "2019-08-30T07:30:00+00:00",
      "bodytext": "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>",
    }

Customizing returned properties
================================

By default all properties of entities are returned. That may not be expected behavior because of performance and
security reasons. You can easily manage properties returned from any endpoint using
:ref:`serialization context groups <serialization_context-groups>`.

