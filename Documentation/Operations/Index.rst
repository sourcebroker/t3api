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

    - The path of the first collection operation is used in the :ref:`main endpoint <operations_main-endpoint>`.
    - The path of the first item operation is used to define resource IRI (``@id`` property).

Supported operation methods
=============================

T3api supports all REST methods

+--------------+-----------------+-------------------+--------------------------------------+
| HTTP Method  | Operation type  | Example URL       | Purpose                              |
+==============+=================+===================+======================================+
| **GET**      | Collection      | /resource         | Reading collection of the items      |
+--------------+-----------------+-------------------+--------------------------------------+
| **GET**      | Item            | /resource/{id}    | Reading single item                  |
+--------------+-----------------+-------------------+--------------------------------------+
| **POST**     | Collection      | /resource         | Creating new item                    |
+--------------+-----------------+-------------------+--------------------------------------+
| **PATCH**    | Item            | /resource/{id}    | Updating the item                    |
+--------------+-----------------+-------------------+--------------------------------------+
| **PUT**      | Item            | /resource/{id}    | Replacing the item                   |
+--------------+-----------------+-------------------+--------------------------------------+
| **DELETE**   | Item            | /resource/{id}    | Deleting the item                    |
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
- ``hydra:search`` - contains data useful for filtering (see more on :ref:`filtering`).

Here is an example of basic response for collection operation. You can open it at following url: https://13.t3api.ddev.site/_api/news/news

.. code-block:: json

    {
      "hydra:member": [
        {
          "title": "[EN] Sed ut perspiciatis unde omnis iste natus error sit voluptatem folder A",
          "alternativeTitle": "",
          "teaser": "",
          "datetime": "2020-05-28T19:20:00.000+00:00",
          "author": "",
          "authorEmail": "",
          "categories": [
            {
              "title": "[EN] Category 1A",
              "image": null,
              "uid": 1,
              "@id": "/_api/news/categories/1"
            },
            {
              "title": "[EN] Category 2A",
              "image": null,
              "uid": 2,
              "@id": "/_api/news/categories/2"
            }
          ],
          "type": "0",
          "falMedia": [
            {
              "url": "https://13.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
              "uid": 4,
              "file": {
                "uid": 1,
                "name": "test1.jpg",
                "mimeType": "image/jpeg",
                "size": 42520
              }
            }
          ],
          "internalurl": "",
          "externalurl": "",
          "istopnews": false,
          "tags": [
            {
              "title": "[EN] Tag 1A",
              "uid": 1,
              "@id": "/_api/news/tags/1"
            },
            {
              "title": "[EN] Tag 4A",
              "uid": 4,
              "@id": "/_api/news/tags/4"
            }
          ],
          "singleUri": "https://13.t3api.ddev.site/news/en-sed-ut-perspiciatis-unde-omnis-iste-natus-error-sit-voluptatem-folder-a",
          "imageThumbnail": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_ce3d0ad685.jpg",
          "imageLarge": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_67bc9e165d.jpg",
          "uid": 1,
          "@id": "/_api/news/news/1"
        },
        {
          "title": "[EN] Natus error sit voluptatem folder A",
          "alternativeTitle": "",
          "teaser": "",
          "datetime": "2020-05-28T19:45:00.000+00:00",
          "author": "",
          "authorEmail": "",
          "categories": [
            {
              "title": "[EN] Category 2A",
              "image": null,
              "uid": 2,
              "@id": "/_api/news/categories/2"
            }
          ],
          "type": "0",
          "falMedia": [
            {
              "url": "https://13.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
              "uid": 2,
              "file": {
                "uid": 1,
                "name": "test1.jpg",
                "mimeType": "image/jpeg",
                "size": 42520
              }
            },
            {
              "url": "https://13.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
              "uid": 3,
              "file": {
                "uid": 1,
                "name": "test1.jpg",
                "mimeType": "image/jpeg",
                "size": 42520
              }
            }
          ],
          "internalurl": "",
          "externalurl": "",
          "istopnews": true,
          "tags": [
            {
              "title": "[EN] Tag 1A",
              "uid": 1,
              "@id": "/_api/news/tags/1"
            },
            {
              "title": "[EN] Tag 2A",
              "uid": 2,
              "@id": "/_api/news/tags/2"
            }
          ],
          "singleUri": "https://13.t3api.ddev.site/news/en-natus-error-sit-voluptatem-folder-a",
          "imageThumbnail": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_ce3d0ad685.jpg",
          "imageLarge": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_67bc9e165d.jpg",
          "uid": 2,
          "@id": "/_api/news/news/2"
        },
        {
          "title": "[EN] Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur folder A",
          "alternativeTitle": "",
          "teaser": "",
          "datetime": "2020-05-29T20:10:00.000+00:00",
          "author": "",
          "authorEmail": "",
          "categories": [
            {
              "title": "[EN] Category 3A",
              "image": null,
              "uid": 3,
              "@id": "/_api/news/categories/3"
            },
            {
              "title": "[EN] Category 4A",
              "image": null,
              "uid": 4,
              "@id": "/_api/news/categories/4"
            }
          ],
          "type": "0",
          "falMedia": [
            {
              "url": "https://13.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
              "uid": 1,
              "file": {
                "uid": 1,
                "name": "test1.jpg",
                "mimeType": "image/jpeg",
                "size": 42520
              }
            }
          ],
          "internalurl": "",
          "externalurl": "",
          "istopnews": false,
          "tags": [
            {
              "title": "[EN] Tag 4A",
              "uid": 4,
              "@id": "/_api/news/tags/4"
            }
          ],
          "singleUri": "https://13.t3api.ddev.site/news/en-ut-enim-ad-minima-veniam-quis-nostrum-exercitationem-ullam-corporis-suscipit-laboriosam-nisi-ut-aliquid-ex-ea-commodi-consequatur-folder-a",
          "imageThumbnail": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_ce3d0ad685.jpg",
          "imageLarge": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_67bc9e165d.jpg",
          "uid": 3,
          "@id": "/_api/news/news/3"
        },
        {
          "title": "Sed ut perspiciatis unde omnis iste natus error sit voluptatem folder B",
          "alternativeTitle": "",
          "teaser": "",
          "datetime": "2020-05-28T19:20:00.000+00:00",
          "author": "",
          "authorEmail": "",
          "categories": [],
          "type": "0",
          "falMedia": [
            {
              "url": "https://13.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
              "uid": 8,
              "file": {
                "uid": 1,
                "name": "test1.jpg",
                "mimeType": "image/jpeg",
                "size": 42520
              }
            }
          ],
          "internalurl": "",
          "externalurl": "",
          "istopnews": false,
          "tags": [
            {
              "title": "Tag 1B",
              "uid": 6,
              "@id": "/_api/news/tags/6"
            },
            {
              "title": "Tag 4B",
              "uid": 9,
              "@id": "/_api/news/tags/9"
            }
          ],
          "singleUri": "https://13.t3api.ddev.site/news/sed-ut-perspiciatis-unde-omnis-iste-natus-error-sit-voluptatem-folder-b",
          "imageThumbnail": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_ce3d0ad685.jpg",
          "imageLarge": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_67bc9e165d.jpg",
          "uid": 5,
          "@id": "/_api/news/news/5"
        },
        {
          "title": "Natus error sit voluptatem folder B",
          "alternativeTitle": "",
          "teaser": "",
          "datetime": "2020-05-28T19:45:00.000+00:00",
          "author": "",
          "authorEmail": "",
          "categories": [],
          "type": "0",
          "falMedia": [
            {
              "url": "https://13.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
              "uid": 6,
              "file": {
                "uid": 1,
                "name": "test1.jpg",
                "mimeType": "image/jpeg",
                "size": 42520
              }
            },
            {
              "url": "https://13.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
              "uid": 7,
              "file": {
                "uid": 1,
                "name": "test1.jpg",
                "mimeType": "image/jpeg",
                "size": 42520
              }
            }
          ],
          "internalurl": "",
          "externalurl": "",
          "istopnews": false,
          "tags": [
            {
              "title": "Tag 1B",
              "uid": 6,
              "@id": "/_api/news/tags/6"
            },
            {
              "title": "Tag 2B",
              "uid": 7,
              "@id": "/_api/news/tags/7"
            }
          ],
          "singleUri": "https://13.t3api.ddev.site/news/natus-error-sit-voluptatem-folder-b",
          "imageThumbnail": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_ce3d0ad685.jpg",
          "imageLarge": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_67bc9e165d.jpg",
          "uid": 6,
          "@id": "/_api/news/news/6"
        },
        {
          "title": "Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur folder B",
          "alternativeTitle": "",
          "teaser": "",
          "datetime": "2020-05-29T20:10:00.000+00:00",
          "author": "",
          "authorEmail": "",
          "categories": [],
          "type": "0",
          "falMedia": [
            {
              "url": "https://13.t3api.ddev.site/fileadmin/user_upload/test1.jpg",
              "uid": 5,
              "file": {
                "uid": 1,
                "name": "test1.jpg",
                "mimeType": "image/jpeg",
                "size": 42520
              }
            }
          ],
          "internalurl": "",
          "externalurl": "",
          "istopnews": false,
          "tags": [
            {
              "title": "Tag 4B",
              "uid": 9,
              "@id": "/_api/news/tags/9"
            }
          ],
          "singleUri": "https://13.t3api.ddev.site/news/ut-enim-ad-minima-veniam-quis-nostrum-exercitationem-ullam-corporis-suscipit-laboriosam-nisi-ut-aliquid-ex-ea-commodi-consequatur-folder-b",
          "imageThumbnail": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_ce3d0ad685.jpg",
          "imageLarge": "https://13.t3api.ddev.site/fileadmin/_processed_/a/7/csm_test1_67bc9e165d.jpg",
          "uid": 7,
          "@id": "/_api/news/news/7"
        }
      ],
      "hydra:totalItems": 6,
      "hydra:view": {
        "hydra:first": "/_api/news/news?page=1",
        "hydra:last": "/_api/news/news?page=1",
        "hydra:pages": [
          "/_api/news/news?page=1"
        ],
        "hydra:page": 1
      },
      "hydra:search": {
        "hydra:template": "/_api/news/news{?order[uid],order[title],order[datetime],istopnews,uid,datetime,pid,search}",
        "hydra:mapping": [
          {
            "variable": "order[uid]",
            "property": "uid"
          },
          {
            "variable": "order[title]",
            "property": "title"
          },
          {
            "variable": "order[datetime]",
            "property": "datetime"
          },
          {
            "variable": "istopnews",
            "property": "istopnews"
          },
          {
            "variable": "uid",
            "property": "uid"
          },
          {
            "variable": "datetime",
            "property": "datetime"
          },
          {
            "variable": "pid",
            "property": "pid"
          },
          {
            "variable": "search",
            "property": "title"
          }
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

.. _operations_main-endpoint:

Main endpoint
================

There is one special build-in endpoint which is not determined by ``@ApiResource`` annotation - Main endpoint.
It contains list all available collection operations. It is useful for creating Postman requests collections
or for frontend applications which avoids to use hardcoded path for the endpoints.
Main endpoint is available under :ref:`base path <getting-started_base-path>` (default ``https://example.com/_api/``).

Example response on the main endpoint for th3 t3api demo is available at: `https://13.t3api.ddev.site/_api <https://13.t3api.ddev.site/_api>`__
and looks like:

.. code-block:: json

    {
      "resources": {
        "SourceBroker\\T3apinews\\Domain\\Model\\File": "/_api/news/files",
        "SourceBroker\\T3apinews\\Domain\\Model\\Tag": "/_api/news/tags",
        "SourceBroker\\T3apinews\\Domain\\Model\\Category": "/_api/news/categories",
        "SourceBroker\\T3apinews\\Domain\\Model\\News": "/_api/news/news"
      }
    }

Class used as a response in main endpoint may be overwritten in ``ext_localconf.php``:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass'] = \Vendor\Ext\MyCustomMainEndpoint::class;

To disable main endpoint it is just needed to set ``mainEndpointResponseClass`` to ``null``. It will result in 404 error response.

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass'] = null;

.. toctree::
   :maxdepth: 3
   :hidden:

   CustomizingOperationHandler/Index
