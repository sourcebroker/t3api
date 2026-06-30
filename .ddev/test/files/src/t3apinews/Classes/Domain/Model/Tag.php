<?php

namespace SourceBroker\T3apinews\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use SourceBroker\T3api\Filter\OrderFilter;

/**
 * @T3api\ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "path"="/news/tags",
 *          },
 *     },
 *     itemOperations={
 *          "get"={
 *              "path"="/news/tags/{id}",
 *          }
 *     },
 * )
 *
 * @T3api\ApiFilter(
 *     OrderFilter::class,
 *     properties={"uid","title"}
 * )
 */
class Tag extends \GeorgRinger\News\Domain\Model\Tag
{
}
