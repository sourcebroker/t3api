<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('t3api', 'Configuration/TypoScript', 'T3api');

        if (TYPO3_MODE === 'BE') {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'SourceBroker.t3api',
                'tools',
                'tx_t3api_m1',
                '',
                ['Administration' => 'documentation, openApiData'],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:t3api/Resources/Public/Icons/module.svg',
                    'labels' => 'LLL:EXT:t3api/Resources/Private/Language/locallang_modadministration.xlf',
                    'inheritNavigationComponentFromMainModule' => false,
                ]
            );
        }
    }
);
