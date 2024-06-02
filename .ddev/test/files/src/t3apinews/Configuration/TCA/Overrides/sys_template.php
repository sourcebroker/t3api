<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
defined('TYPO3') or die();

ExtensionManagementUtility::addStaticFile('t3apinews', 'Configuration/TypoScript', 'T3api sample for news ext');
