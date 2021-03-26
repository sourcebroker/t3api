<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Configuration\CorsOptions;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CorsService
{
    public function getOptions(): CorsOptions
    {
        return GeneralUtility::makeInstance(CorsOptions::class, Configuration::getCors());
    }

    public function isAllowedOrigin(string $origin, CorsOptions $options): bool
    {
        if ($this->isWildcard($options->allowOrigin)) {
            return true;
        }

        if ($options->originRegex) {
            foreach ($options->allowOrigin as $originRegexp) {
                if (preg_match('{'.$originRegexp.'}i', $origin)) {
                    return true;
                }
            }
        } else if (in_array($origin, $options->allowOrigin, false)) {
            return true;
        }

        return false;
    }

    public function isWildcard($option): bool
    {
        return $option === true
            || (is_array($option) && in_array('*', $option, true))
            || (is_string($option) && $option === '*');
    }
}
