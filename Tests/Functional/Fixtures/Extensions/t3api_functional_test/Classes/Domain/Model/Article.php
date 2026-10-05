<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "post": {
 *             "method": "POST",
 *             "path": "/articles",
 *             "normalizationContext": {
 *                 "groups": {"api_article"}
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/articles/{id}",
 *             "normalizationContext": {
 *                 "groups": {"api_article"}
 *             }
 *         },
 *         "patch": {
 *             "method": "PATCH",
 *             "path": "/articles/{id}",
 *             "normalizationContext": {
 *                 "groups": {"api_article"}
 *             }
 *         }
 *     }
 * )
 */
class Article extends AbstractEntity
{
    protected string $title = '';

    /**
     * Typed and not initialized on purpose - such properties are not returned by `_getProperties()`.
     */
    protected ?\DateTime $crdate;

    /**
     * Typed, not initialized and not nullable on purpose - getter fails when value is not set.
     */
    protected \DateTime $tstamp;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCrdate(): ?\DateTime
    {
        return $this->crdate;
    }

    public function getTstamp(): \DateTime
    {
        return $this->tstamp;
    }
}
