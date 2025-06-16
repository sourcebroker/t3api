<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Hook;

use SourceBroker\T3api\Service\RouteService;

// @todo remove this class when support for TYPO3 v12 is dropped. For TYPO3 >= v13 \SourceBroker\T3api\EventListener\EnrichPageCacheIdentifierParametersEventListener is doing the same job.
class EnrichHashBase
{
    /**
     * We add random value to hash base to protect against full page cache which causes at least two known issues in
     * production environment:
     * 1. Extbase framework configuration is not loaded thus tables mapping is unknown and queries to not existing database tables may be done.
     * 2. Links generated using link handler are not build correctly.
     */
    public function init(array &$params): void
    {
        if (RouteService::routeHasT3ApiResourceEnhancerQueryParam()) {
            $params['hashParameters']['t3api_hash_base_random'] = microtime();
        }
    }
}
