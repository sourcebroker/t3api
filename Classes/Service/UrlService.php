<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

class UrlService
{
    public static function forceAbsoluteUrl(string $url, string $host): string
    {
        if (parse_url($url, PHP_URL_HOST) === null) {
            return rtrim($host, '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }
}
