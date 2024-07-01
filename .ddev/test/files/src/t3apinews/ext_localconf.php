<?php

defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3apinews/Configuration/TsConfig/Page/mod.tsconfig">');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] = array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] ?? [],
            [
                'GeorgRinger\News' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('t3apinews') . 'Resources/Private/Serializer/GeorgRinger.News',
            ]
        );

    }
);
