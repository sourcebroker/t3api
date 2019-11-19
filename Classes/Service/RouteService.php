<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class RouteService
 */
class RouteService implements SingletonInterface
{
    /**
     * Returns base path without prefix and trailing slashes
     *
     * @return string
     */
    public static function getApiBasePath(): string
    {
        return trim($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['basePath'], '/');
    }
}
