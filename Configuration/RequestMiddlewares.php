<?php

return [
    'frontend' => [
        'sourcebroker/t3api/process-api-request' => [
            'target' => \SourceBroker\T3api\Middleware\T3apiRequestResolver::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
        ],
    ],
];
