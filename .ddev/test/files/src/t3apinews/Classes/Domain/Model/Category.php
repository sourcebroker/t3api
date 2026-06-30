<?php

namespace SourceBroker\T3apinews\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use SourceBroker\T3api\Filter\OrderFilter;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * @T3api\ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "path"="/news/categories",
 *              "normalizationContext"={
 *                  "groups"={"api_get_collection_t3apinews_category"}
 *              },
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/news/categories",
 *              "normalizationContext"={
 *                  "groups"={"api_post_item_t3apinews_category"}
 *              },
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "path"="/news/categories/{id}",
 *              "normalizationContext"={
 *                  "groups"={"api_get_item_t3apinews_category"}
 *              },
 *          },
 *          "delete"={
 *              "method"="DELETE",
 *              "path"="/news/categories/{id}",
 *          },
 *     },
 *     attributes={
 *          "persistence"={
 *              "storagePid"="3",
 *              "recursive"=1
 *          }
 *     }
 * )
 *
 * @T3api\ApiFilter(
 *     OrderFilter::class,
 *     properties={"uid","title"}
 * )
 */
class Category extends \GeorgRinger\News\Domain\Model\Category
{
    public function getImage(): ?FileReference
    {
        return $this->getImages()->count() ? $this->getImages()->toArray()[0] : null;
    }

}
