.. _pagination_server-side:

===============
Server side
===============

Server side pagination is controlled with ``pagination_enabled`` setting (enabled by default). When it is enabled every collection ``GET`` response contains at most ``pagination_items_per_page`` items and the client chooses the page with ``page`` query param (name can be changed with ``page_parameter_name``).

To disable pagination for a resource (e.g. small dictionaries which should always be returned in full) set ``pagination_enabled`` to ``false``:

.. code-block:: php

   use SourceBroker\T3api\Annotation as T3api;

   /**
    * @T3api\ApiResource(
    *     collectionOperations={
    *          "get"={
    *              "path"="/news/tags",
    *          },
    *     },
    *     attributes={
    *          "pagination_enabled"=false,
    *     }
    * )
    */
   class Tag extends \GeorgRinger\News\Domain\Model\Tag
   {
   }

To change the name of the page param or the number of items on page:

.. code-block:: php

   /**
    * @T3api\ApiResource(
    *     ...
    *     attributes={
    *          "pagination_items_per_page"=12,
    *          "page_parameter_name"="p",
    *     }
    * )
    */

Now the third page is available under ``?p=3``.

.. note::

   Requesting a page beyond the last one returns an empty ``hydra:member`` list (still with correct ``hydra:totalItems``), not an error.

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | Get first page of news (news resource returns 20 items per page):
     | `https://14.t3api.ddev.site/_api/news/news?page=1 <https://14.t3api.ddev.site/_api/news/news?page=1>`__
     |
   * | Get page which does not exist - ``hydra:member`` is empty:
     | `https://14.t3api.ddev.site/_api/news/news?page=5 <https://14.t3api.ddev.site/_api/news/news?page=5>`__
     |
