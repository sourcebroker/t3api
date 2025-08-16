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
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $title = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $alternativeTitle = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $teaser = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $bodytext = '';

    /**
     * @var \DateTime
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $datetime;

    /**
     * @var \Datetime
     * @T3api\Serializer\Groups({
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $crdate;

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $author = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $authorEmail = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3apinews\Domain\Model\Category>
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3apinews\Domain\Model\News>
     * @T3api\Serializer\Groups({
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $related;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3apinews\Domain\Model\News>
     * @T3api\Serializer\Groups({
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $relatedFrom;

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $type = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $pathSegment = '';
    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $internalurl = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $externalurl = '';

    /**
     * @var bool
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected $istopnews = false;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3apinews\Domain\Model\Tag>
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     */
    protected \TYPO3\CMS\Extbase\Persistence\ObjectStorage $tags;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3apinews\Domain\Model\FileReference>
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_post_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     *     "api_put_item_t3apinews_news",
     * })
     * @T3api\ORM\Cascade("persist")
     */
    protected $falMedia;

    /**
     * @T3api\Serializer\VirtualProperty()
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     * })
     * @T3api\Serializer\Type\RecordUri("tx_news")
     */
    public function getSingleUri()
    {
        // need to return non null value
        return '';
    }

    /**
     * @T3api\Serializer\VirtualProperty()
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     * })
     * @T3api\Serializer\Type\Image(width=380, height="250c")
     */
    public function getImageThumbnail(): ?FileReference
    {
        return $this->getFalMedia()->count() ? $this->getFalMedia()->toArray()[0] : null;
    }

    /**
     * @T3api\Serializer\VirtualProperty()
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     * })
     * @T3api\Serializer\Type\Image(width=1280, height=768)
     */
    public function getImageLarge(): ?FileReference
    {
        return $this->getFalMedia()->count() ? $this->getFalMedia()->toArray()[0] : null;
    }

}
