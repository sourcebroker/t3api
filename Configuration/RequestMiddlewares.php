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
    // Keeps API query arguments (filters, pagination) away from cHash validation, see class docblock.
    'sourcebroker/t3api/page-arguments-validation-bypass' => [
        'target' => \SourceBroker\T3api\Middleware\T3apiPageArgumentsValidationBypass::class,
        'after' => [
            'typo3/cms-frontend/page-resolver',
        ],
        'before' => [
            'typo3/cms-frontend/page-argument-validator',
        ],
    ],
    'sourcebroker/t3api/page-arguments-restorer' => [
        'target' => \SourceBroker\T3api\Middleware\T3apiPageArgumentsRestorer::class,
        'after' => [
            'typo3/cms-frontend/page-argument-validator',
        ],
        'before' => [
            // `typo3/cms-frontend/tsfe` (directly after the validator) does not exist since TYPO3 v14
            (new Typo3Version())->getMajorVersion() < 14
                ? 'typo3/cms-frontend/tsfe'
                : 'typo3/cms-frontend/prepare-tsfe-rendering',
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
