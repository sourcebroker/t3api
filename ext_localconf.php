<?php

defined('TYPO3_MODE') || die('Access denied.');

// @todo make path configurable and change all usages
define('RESTIFY_BASE_PATH', '/_api/');

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['restify'] =
            \SourceBroker\Restify\Dispatcher\Bootstrap::class . '::process';

        if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            if (\TYPO3\CMS\Core\Utility\StringUtility::beginsWith($_SERVER['REQUEST_URI'], RESTIFY_BASE_PATH)) {
                $_GET['eID'] = 'restify';
            }
        }
    }
);
