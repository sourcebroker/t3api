.. _pagination:

===========
Pagination
===========

Collection ``GET`` operations are paginated by default. Number of items on a page, possibility to change it by the client and possibility to disable pagination by the client are configurable globally and per resource.

Pagination data is returned in ``hydra:view`` of collection response, e.g. for ``/_api/news/news?itemsPerPage=2&page=2``:

.. code-block:: json

   {
       "hydra:member": [ ... ],
       "hydra:totalItems": 6,
       "hydra:view": {
           "hydra:first": "/_api/news/news?itemsPerPage=2&page=1",
           "hydra:last": "/_api/news/news?itemsPerPage=2&page=3",
           "hydra:prev": "/_api/news/news?itemsPerPage=2&page=1",
           "hydra:next": "/_api/news/news?itemsPerPage=2&page=3",
           "hydra:pages": [
               "/_api/news/news?itemsPerPage=2&page=1",
               "/_api/news/news?itemsPerPage=2&page=2",
               "/_api/news/news?itemsPerPage=2&page=3"
           ],
           "hydra:page": 2
       },
       "hydra:search": { ... }
   }

``hydra:prev`` is skipped on the first page and ``hydra:next`` is skipped on the last page. All other query params (e.g. filters) are kept in the generated links. ``hydra:view`` is empty if pagination is disabled.

Available settings
===================

.. list-table::
   :header-rows: 1
   :widths: 30 15 55

   * - Setting
     - Default
     - Description
   * - ``pagination_enabled``
     - ``true``
     - Enables pagination on the server side. See :ref:`pagination_server-side`.
   * - ``pagination_items_per_page``
     - ``30``
     - Number of items returned on a single page.
   * - ``maximum_items_per_page``
     - ``9999999``
     - Upper limit of items on a single page. Applies also to the number requested by the client.
   * - ``page_parameter_name``
     - ``page``
     - Name of the query param holding the number of the requested page.
   * - ``pagination_client_enabled``
     - ``false``
     - Allows client to enable / disable pagination with query param. See :ref:`pagination_client-side`.
   * - ``enabled_parameter_name``
     - ``pagination``
     - Name of the query param used by the client to enable / disable pagination.
   * - ``pagination_client_items_per_page``
     - ``false``
     - Allows client to change number of items per page with query param.
   * - ``items_per_page_parameter_name``
     - ``itemsPerPage``
     - Name of the query param used by the client to change number of items per page.

Global configuration
======================

Default values are kept in ``$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination']``. They can be changed e.g. in ``ext_localconf.php`` of your extension:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination'] = array_merge(
       $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination'],
       [
           'pagination_items_per_page' => 10,
           'maximum_items_per_page' => 100,
       ]
   );

Resource specific configuration
================================

Each of the settings can be overridden for a single resource in ``attributes`` of ``ApiResource`` annotation. Example below is the configuration used by news resource in testing instance:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource(
    *     collectionOperations={
    *          "get"={
    *              "path"="/news/news",
    *          },
    *     },
    *     attributes={
    *          "pagination_client_enabled"=true,
    *          "pagination_items_per_page"=20,
    *          "maximum_items_per_page"=100,
    *          "pagination_client_items_per_page"=true,
    *     }
    * )
    */
   class News extends \GeorgRinger\News\Domain\Model\News
   {
   }

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | Get second page of news with 2 items per page:
     | `https://14.t3api.ddev.site/_api/news/news?itemsPerPage=2&page=2 <https://14.t3api.ddev.site/_api/news/news?itemsPerPage=2&page=2>`__
     |
   * | Get all news without pagination:
     | `https://14.t3api.ddev.site/_api/news/news?pagination=0 <https://14.t3api.ddev.site/_api/news/news?pagination=0>`__
     |

.. toctree::
   :maxdepth: 3
   :hidden:

   ServerSide/Index
   ClientSide/Index
