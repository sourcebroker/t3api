<?php

namespace SourceBroker\T3apinews\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use SourceBroker\T3api\Annotation as T3api;
use SourceBroker\T3api\Filter\OrderFilter;
use SourceBroker\T3api\Filter\BooleanFilter;
use SourceBroker\T3api\Filter\NumericFilter;
use SourceBroker\T3api\Filter\SearchFilter;
use SourceBroker\T3api\Filter\RangeFilter;
use SourceBroker\T3api\Filter\UidFilter;

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

    /**
     * Need to return non-null value for virtual property configured in YAML.
     */
    public function getSingleUri()
    {
        return '';
    }

    public function getImageThumbnail(): ?FileReference
    {
        return $this->getFalMedia()->count() ? $this->getFalMedia()->toArray()[0] : null;
    }

    public function getImageLarge(): ?FileReference
    {
        return $this->getFalMedia()->count() ? $this->getFalMedia()->toArray()[0] : null;
    }

}
