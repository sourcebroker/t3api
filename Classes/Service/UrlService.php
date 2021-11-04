<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

class UrlService
{
    public static function forceAbsoluteUrl(string $url, string $host): string
    {
        if (strpos($url, '//') !== 0 && parse_url($url, PHP_URL_SCHEME) === null) {
            return rtrim($host, '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }
}