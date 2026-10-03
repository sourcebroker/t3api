.. _pagination_client-side:

===============
Client side
===============

By default client can only choose the page number. Two more options can be opened to the client per resource (or globally):

- ``pagination_client_enabled`` - client can enable or disable pagination by sending ``?pagination=1`` or ``?pagination=0`` (param name can be changed with ``enabled_parameter_name``). If the param is not sent, ``pagination_enabled`` setting decides.
- ``pagination_client_items_per_page`` - client can change number of items per page by sending ``?itemsPerPage=<int>`` (param name can be changed with ``items_per_page_parameter_name``).

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
    *          "pagination_client_items_per_page"=true,
    *          "maximum_items_per_page"=100,
    *     }
    * )
    */
   class News extends \GeorgRinger\News\Domain\Model\News
   {
   }

.. important::

   Always set ``maximum_items_per_page`` when you enable ``pagination_client_items_per_page``. Number of items requested by the client is never higher than ``maximum_items_per_page``, so ``?itemsPerPage=100000`` returns 100 items with configuration above. Keep in mind that ``pagination_client_enabled`` allows the client to fetch the whole collection with ``?pagination=0`` regardless of ``maximum_items_per_page``.

Number of items per page has to be an integer greater than or equal to 0, otherwise ``400 Bad Request`` is returned. ``?itemsPerPage=0`` returns no items, only ``hydra:totalItems`` - useful when the client needs only the number of all items. It can not be combined with a page greater than 1.

.. admonition:: Real examples. Run "ddev restart && ddev ci 14" and try those links below.

   * | Get 2 news per page, second page:
     | `https://14.t3api.ddev.site/_api/news/news?itemsPerPage=2&page=2 <https://14.t3api.ddev.site/_api/news/news?itemsPerPage=2&page=2>`__
     |
   * | Get 4 news, sorted by title, from page 3 or 6 only - filters, sorting and pagination can be combined:
     | `https://14.t3api.ddev.site/_api/news/news?pid[]=3&pid[]=6&order[title]=asc&itemsPerPage=4 <https://14.t3api.ddev.site/_api/news/news?pid[]=3&pid[]=6&order[title]=asc&itemsPerPage=4>`__
     |
   * | Get only the number of all news (``hydra:member`` is empty):
     | `https://14.t3api.ddev.site/_api/news/news?itemsPerPage=0 <https://14.t3api.ddev.site/_api/news/news?itemsPerPage=0>`__
     |
   * | Disable pagination - ``hydra:view`` is empty and all news are returned:
     | `https://14.t3api.ddev.site/_api/news/news?pagination=0 <https://14.t3api.ddev.site/_api/news/news?pagination=0>`__
     |
