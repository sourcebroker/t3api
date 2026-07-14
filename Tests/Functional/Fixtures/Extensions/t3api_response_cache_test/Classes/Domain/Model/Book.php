<?php

declare(strict_types=1);

namespace T3apiTests\ResponseCacheTest\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @ApiResource(
 *     attributes={
 *         "cache": {
 *             "lifetime": 3600,
 *             "readCondition": "request.query.get('nocache') == null",
 *             "writeCondition": "request.query.get('nocache') == null"
 *         }
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/books",
 *             "security": "1 == 1",
 *             "attributes": {
 *                 "cache": {
 *                     "tags": {"functional_custom_tag"},
 *                     "memberTagExpressions": {"'author_' ~ object.getAuthor().getUid()"}
 *                 }
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/books/{id}"
 *         }
 *     }
 * )
 */
class Book extends AbstractEntity
{
    protected string $title = '';

    protected ?Author $author = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }
}
