<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        if (TYPO3_MODE === 'BE') {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'SourceBroker.t3api',
                'tools',
                'm1',
                '',
                [
                    'Administration' => 'documentation',
                    'OpenApi' => 'display, spec',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:t3api/Resources/Public/Icons/Extension.svg',
                    'labels' => 'LLL:EXT:t3api/Resources/Private/Language/locallang_modadministration.xlf',
                    'inheritNavigationComponentFromMainModule' => false,
                ]
            );
        }
    }
);
