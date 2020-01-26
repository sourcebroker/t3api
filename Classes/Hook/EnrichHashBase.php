<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Hook;

use TYPO3\CMS\Core\Http\ServerRequest;

class EnrichHashBase
{
    /**
     * We add random value to hash base to protect against full page cache which causes at least two known issues in
     * production environment:
     * 1. Extbase framework configuration is not loaded thus tables mapping is unknown and queries to not existing database tables may be done.
     * 2. Links generated using link handler are not build correctly.
     *
     * @param array $params
     */
    public function init(array &$params): void
    {
        /** @var ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        if (is_array($request->getQueryParams()) && array_key_exists('t3apiResource', $request->getQueryParams())) {
            $params['hashParameters']['t3api_hash_base_random'] = microtime();
        }
    }
}
