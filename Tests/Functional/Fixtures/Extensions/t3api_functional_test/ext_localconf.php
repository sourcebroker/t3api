<?php

defined('TYPO3') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] = array_merge(
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] ?? [],
    [
        't3api_functional_test' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('t3api_functional_test')
            . 'Resources/Private/Serializer',
    ]
);
