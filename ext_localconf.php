<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3') || die('Access denied.');

call_user_func(
    static function () {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['basePath'] = '_api';
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader'] = 'X-Locale';

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] = [
            't3api' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('t3api') . 'Resources/Private/Serializer/Metadata',
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['forceEntityProperties'] = [
            'uid',
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'] = \SourceBroker\T3api\Response\HydraCollectionResponse::class;

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass'] = \SourceBroker\T3api\Response\MainEndpointResponse::class;

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination'] = [
            'pagination_enabled' => true,
            'pagination_client_enabled' => false,
            'pagination_items_per_page' => 30,
            'maximum_items_per_page' => 9999999,
            'pagination_client_items_per_page' => false,
            'items_per_page_parameter_name' => 'itemsPerPage',
            'enabled_parameter_name' => 'pagination',
            'page_parameter_name' => 'page',
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowCredentials'] = false;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowOrigin'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowHeaders'] = [];
        // simple headers are always accepted. They are kept in separate element than `allowHeaders` to avoid mistakenly override by 3rd party extensions
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['simpleHeaders'] = [
            'Accept',
            'Accept-Language',
            'Content-Language',
            'Origin',
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader'],
        ];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['allowMethods'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['exposeHeaders'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['maxAge'] = 0;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors']['originRegex'] = false;

        if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3api']) || !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3api'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3api'] = [
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
                'options' => [
                    'defaultLifetime' => 0,
                ],
                'groups' => ['system'],
            ];
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue'] = [
            TYPO3\CMS\Core\Resource\FileReference::class => [
                \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException::class,
                \UnexpectedValueException::class,
            ],
            TYPO3\CMS\Extbase\Domain\Model\FileReference::class => [
                \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException::class,
                \UnexpectedValueException::class,
            ],
        ];

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['t3api_clearcache'] =
            \SourceBroker\T3api\Service\SerializerService::class . '->clearCache';

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['createHashBase']['t3api'] =
            \SourceBroker\T3api\Hook\EnrichHashBase::class . '->init';

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][\SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::ENHANCER_NAME] = \SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::class;

        // protects against "&cHash empty" error when `cacheHash.enforceValidation` is set to `true`
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'])) {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = [\SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::PARAMETER_NAME];
        } elseif (is_array($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'])) {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = \SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::PARAMETER_NAME;
        } elseif (is_string($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'])) {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] .= ',' . \SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::PARAMETER_NAME;
        }
    }
);
