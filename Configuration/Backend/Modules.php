<?php

return [
    'tools_t3api' => [
        'parent' => 'tools',
        'position' => ['before' => '*'],
        'access' => 'group,user',
        'iconIdentifier' => 'extension-t3api',
        'labels' => 'LLL:EXT:t3api/Resources/Private/Language/locallang_modadministration.xlf:mlang_tabs_tab',
        'inheritNavigationComponentFromMainModule' => false,
        'routes' => [
            '_default' => [
                'target' => \SourceBroker\T3api\Controller\AdministrationController::class . '::handleRequest',
            ],
        ],
    ],
];
