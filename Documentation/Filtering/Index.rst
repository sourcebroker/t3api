.. _filtering:

=========
Filtering
=========

Filters gives possibility to customize SQL query used to receive results in ``GET`` collection operation. Information about filters available for operation are returned inside ``hydra:search`` section in response body. To register filter for resource it is needed to add appropriate annotation:

.. code-block:: php

    /**
     * @T3api\ApiResource(
     *     collectionOperations={
     *          "get"={
     *              "method"="GET",
     *              "path"="/news/news",
     *              "normalizationContext"={
     *                  "groups"={"api_get_collection_t3apinews_news"}
     *              },
     *          },
     *          "post"={
     *              "method"="POST",
     *              "path"="/news/news",
     *              "normalizationContext"={
     *                  "groups"={"api_post_item_t3apinews_news"}
     *              },
     *          },
     *     },
     *     itemOperations={
     *          "get"={
     *              "path"="/news/news/{id}",
     *              "normalizationContext"={
     *                  "groups"={"api_get_item_t3apinews_news"}
     *              },
     *          },
     *          "patch"={
     *              "method"="PATCH",
     *              "path"="/news/news/{id}",
     *              "normalizationContext"={
     *                  "groups"={"api_patch_item_t3apinews_news"}
     *              },
     *          },
     *          "put"={
     *              "method"="PUT",
     *              "path"="/news/news/{id}",
     *              "normalizationContext"={
     *                  "groups"={"api_put_item_t3apinews_news"}
     *              },
     *          },
     *          "delete"={
     *              "method"="DELETE",
     *              "path"="/news/news/{id}",
     *          },
     *     },
     *     attributes={
     *          "pagination_client_enabled"=true,
     *          "pagination_items_per_page"=20,
     *          "maximum_items_per_page"=100,
     *          "pagination_client_items_per_page"=true,
     *          "persistence"={
     *              "storagePid"="3,6",
     *              "recursive"=1
     *          }
     *     }
     * )
     *
     * @T3api\ApiFilter(
     *     OrderFilter::class,
     *     properties={"uid","title","datetime"}
     * )
     *
     * @T3api\ApiFilter(
     *     BooleanFilter::class,
     *     properties={"istopnews"}
     * )
     *
     * @T3api\ApiFilter(
     *     UidFilter::class,
     *     properties={"uid"}
     * )
     *
     * @T3api\ApiFilter(
     *     RangeFilter::class,
     *     properties={
     *       "datetime": "datetime"
     *     }
     * )
     *
     * @T3api\ApiFilter(
     *     NumericFilter::class,
     *     properties={"pid"}
     * )
     *
     * @T3api\ApiFilter(
     *     SearchFilter::class,
     *     properties={
     *          "title": "partial",
     *          "alternativeTitle": "partial",
     *          "bodytext": "partial",
     *          "tags.title": "partial",
     *     },
     *     arguments={
     *          "parameterName": "search",
     *     }
     * )
     */
    class News extends \GeorgRinger\News\Domain\Model\News
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

