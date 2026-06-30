<?php
namespace SourceBroker\T3apinews\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;

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
 *              "folder"="1:/user_upload/media-export-excluded/",
 *              "allowedFileExtensions"={"jpg", "jpeg", "png"},
 *              "conflictMode"="rename",
 *          }
 *     }
 * )
 */
class File extends \TYPO3\CMS\Extbase\Domain\Model\File
{
}
