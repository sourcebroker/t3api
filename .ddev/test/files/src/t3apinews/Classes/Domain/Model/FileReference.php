<?php

namespace SourceBroker\T3apinews\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;

/**
 * Class FileReference
 */
class FileReference extends \GeorgRinger\News\Domain\Model\FileReference
{
    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     * })
     */
    protected $title = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_item_t3apinews_news",
     * })
     */
    protected $description = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     * })
     */
    protected $alternative = '';

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     * })
     * @T3api\Serializer\Type\Typolink
     */
    protected $link = '';

    /**
     * @var int
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     *     "api_patch_item_t3apinews_news",
     * })
     */
    protected $showinpreview = 0;

    /**
     * @T3api\Serializer\VirtualProperty()
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     * })
     * @T3api\Serializer\Type\Image(width=380, height="250c")
     */
    public function getImageThumbnail(): int
    {
        return $this->uid;
    }

    /**
     * @T3api\Serializer\VirtualProperty()
     * @T3api\Serializer\Groups({
     *     "api_get_collection_t3apinews_news",
     *     "api_get_item_t3apinews_news",
     * })
     * @T3api\Serializer\Type\Image()
     */
    public function getImageOriginal(): int
    {
        return $this->uid;
    }
}
