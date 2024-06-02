<?php
namespace SourceBroker\T3apinews\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;

/**
 * @T3api\ApiResource (
 *     collectionOperations={
 *          "post"={
 *              "path"="/news/files",
 *              "method"="POST",
 *          },
 *     },
 *     attributes={
 *          "upload"={
 *              "folder"="1:/user_upload/news-media/",
 *              "allowedFileExtensions"={"jpg", "jpeg", "png"},
 *              "conflictMode"=DuplicationBehavior::RENAME,
 *          }
 *     }
 * )
 */
class File extends \TYPO3\CMS\Extbase\Domain\Model\File
{
}
