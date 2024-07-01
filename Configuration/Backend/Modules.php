<?php

return [
    't3api' => [
        'parent' => 'tools',
        'position' => ['before' => '*'],
        'access' => 'group,user',
        'iconIdentifier' => 'ext-t3api',
        'labels' => 'LLL:EXT:t3api/Resources/Private/Language/locallang_modadministration.xlf:mlang_tabs_tab',
        'inheritNavigationComponentFromMainModule' => false,
        'path' => '/module/t3api',
        'routes' => [
            '_default' => [
                'target' => \SourceBroker\T3api\Controller\AdministrationController::class . '::documentationAction',
            ],
            'open_api_resources' => [
                'target' => \SourceBroker\T3api\Controller\OpenApiController::class . '::resourcesAction',
            ],
        ],
    ],
];
