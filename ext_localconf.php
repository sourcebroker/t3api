<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][\SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer::ENHANCER_NAME] =
            \SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer::class;
    }
);
