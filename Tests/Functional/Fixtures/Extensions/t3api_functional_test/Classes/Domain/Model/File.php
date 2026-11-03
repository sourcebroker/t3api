<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;
use SourceBroker\T3api\Annotation as T3api;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get": {
 *             "path": "/files",
 *             "normalizationContext": {
 *                 "fileTokens": true
 *             }
 *         },
 *         "post": {
 *             "method": "POST",
 *             "path": "/files"
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/files/{id}"
 *         }
 *     },
 *     attributes={
 *         "upload": {
 *             "folder": "1:/user_upload/t3api-test/",
 *             "allowedFileExtensions": {"png"},
 *             "conflictMode": "rename"
 *         }
 *     }
 * )
 */
class File extends \TYPO3\CMS\Extbase\Domain\Model\File
{
    /**
     * @T3api\Serializer\VirtualProperty("url")
     */
    public function getUrl(): ?string
    {
        return $this->getOriginalResource()->getPublicUrl();
    }

    /**
     * @T3api\Serializer\VirtualProperty("name")
     */
    public function getName(): string
    {
        return $this->getOriginalResource()->getName();
    }
}
