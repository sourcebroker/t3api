<?php

defined('TYPO3_MODE') || die('Access denied.');

// @todo make path configurable and change all usages
define('RESTIFY_BASE_PATH', '/_api/');

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers']['RestifyResourceEnhancer'] =
            \SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer::class;
    }
);
