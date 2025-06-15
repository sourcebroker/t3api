<?php

declare(strict_types=1);

namespace SourceBroker\T3api\EventListener;

use SourceBroker\T3api\Service\RouteService;
use TYPO3\CMS\Frontend\Event\BeforePageCacheIdentifierIsHashedEvent;

/**
 * We add random value to page cache identifier parameters ("hash base") to protect against full page cache which causes at least two known issues in production environment:
 * 1. Extbase framework configuration is not loaded thus tables mapping is unknown and queries to not existing database tables may be done.
 * 2. Links generated using link handler are not build correctly.
 * 3. Error (Since TYPO3 v13): "Setup array has not been initialized. This happens in cached Frontend scope where full TypoScript is not needed by the system"
 */
class EnrichPageCacheIdentifierParametersEventListener
{
    public function __invoke(BeforePageCacheIdentifierIsHashedEvent $beforePageCacheIdentifierIsHashedEvent): void
    {
        if (!RouteService::routeHasT3ApiResourceEnhancerQueryParam()) {
            return;
        }

        $beforePageCacheIdentifierIsHashedEvent->setPageCacheIdentifierParameters([
            ...$beforePageCacheIdentifierIsHashedEvent->getPageCacheIdentifierParameters(),
            't3api_hash_base_random' => microtime(),
        ]);
    }
}
