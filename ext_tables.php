<?php

defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {
        // This has effect only in TYPO3 11
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'T3api',
            'tools',
            'm1',
            '',
            [
                \SourceBroker\T3api\Controller\AdministrationController::class => 'documentation',
                \SourceBroker\T3api\Controller\OpenApiController::class => 'display, spec',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:t3api/Resources/Public/Icons/Extension.svg',
                'labels' => 'LLL:EXT:t3api/Resources/Private/Language/locallang_modadministration.xlf',
                'inheritNavigationComponentFromMainModule' => false,
            ]
        );
    }
);
