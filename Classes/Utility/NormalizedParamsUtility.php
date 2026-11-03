<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\NormalizedParams;

/**
 * Replaces `GeneralUtility::getIndpEnv()`, deprecated since TYPO3 14.3.
 *
 * @internal
 */
class NormalizedParamsUtility
{
    /**
     * Returns normalized params of `$GLOBALS['TYPO3_REQUEST']`. Without them (e.g. CLI or a request created by hand)
     * they are created from `$_SERVER`, like `GeneralUtility::getIndpEnv()` did.
     */
    public static function get(): NormalizedParams
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $normalizedParams = $request instanceof ServerRequestInterface
            ? $request->getAttribute('normalizedParams')
            : null;

        return $normalizedParams instanceof NormalizedParams
            ? $normalizedParams
            : new NormalizedParams(
                $_SERVER,
                $GLOBALS['TYPO3_CONF_VARS']['SYS'] ?? [],
                Environment::getCurrentScript(),
                Environment::getPublicPath()
            );
    }
}
