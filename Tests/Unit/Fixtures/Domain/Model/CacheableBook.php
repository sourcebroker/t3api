<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Declares no operations so building an `ApiResource` from this fixture in a unit test never
 * reaches `AbstractOperation`'s `RouteService::getFullApiBasePath()` call, which needs a
 * bootstrapped TYPO3 site unavailable here.
 *
 * @ApiResource(
 *     attributes={
 *         "cache": {
 *             "lifetime": 3600,
 *             "readCondition": "request.query.get('refresh') == null",
 *             "identifierExpressions": {
 *                 "context.getPropertyFromAspect('frontend.user', 'groupIds', '')"
 *             }
 *         }
 *     }
 * )
 */
class CacheableBook extends AbstractEntity
{
    protected string $title = '';
}
