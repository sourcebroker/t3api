<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */

use TYPO3\CMS\Core\Information\Typo3Version;

$frontendMiddlewares = [
    'sourcebroker/t3api/process-api-request' => [
        'target' => \SourceBroker\T3api\Middleware\T3apiRequestResolver::class,
        'after' => [
            'typo3/cms-frontend/prepare-tsfe-rendering',
        ],
        'before' => [
            'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
        ],
    ],
];

// Only required on TYPO3 v12: applies the X-Locale-style header language override before TSFE
// is built. Remove this middleware (and the T3apiRequestLanguageResolver class) entirely once
// TYPO3 v12 support is dropped.
if ((new Typo3Version())->getMajorVersion() < 13) {
    $frontendMiddlewares['sourcebroker/t3api/prepare-api-request'] = [
        'target' => \SourceBroker\T3api\Middleware\T3apiRequestLanguageResolver::class,
        'after' => [
            'typo3/cms-frontend/site',
        ],
        'before' => [
            'typo3/cms-frontend/tsfe',
        ],
    ];
}

return [
    'frontend' => $frontendMiddlewares,
];
