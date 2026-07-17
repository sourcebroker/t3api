<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Declares no resource-level `cache` block (base stays disabled) but enables caching on a single
 * operation only, via a non-empty `attributes.cache` block without an explicit `enabled` key -
 * exercises the "per-operation-only enabling" case in `ApiResourceFactoryTest`.
 *
 * @ApiResource(
 *     itemOperations={
 *         "get": {
 *             "attributes": {
 *                 "cache": {"lifetime": 60}
 *             }
 *         }
 *     }
 * )
 */
class OperationCacheableBook extends AbstractEntity
{
    protected string $title = '';
}
