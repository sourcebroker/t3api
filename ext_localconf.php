<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][\SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::ENHANCER_NAME] = \SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::class;

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['basePath'] = '_api';

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerHandlers'] = [
            \SourceBroker\T3api\Serializer\Handler\AbstractDomainObjectHandler::class,
            \SourceBroker\T3api\Serializer\Handler\ObjectStorageHandler::class,
            \SourceBroker\T3api\Serializer\Handler\FileReferenceHandler::class,
            \SourceBroker\T3api\Serializer\Handler\ImageHandler::class,
            \SourceBroker\T3api\Serializer\Handler\RecordUriHandler::class,
            \SourceBroker\T3api\Serializer\Handler\TypolinkHandler::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'] = [
            SourceBroker\T3api\Serializer\Subscriber\AbstractEntitySubscriber::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] = [
            'TYPO3\CMS\Extbase' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('t3api') . 'Resources/Private/Serializer/TYPO3.CMS.Extbase',
            'TYPO3\CMS\Core' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('t3api') . 'Resources/Private/Serializer/TYPO3.CMS.Core',
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

        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3api'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['t3api'] = [
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
                'options' => [
                    'defaultLifetime' => 0,
                ],
                'groups' => ['system'],
            ];
        }

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['t3api_clearcache'] =
            \SourceBroker\T3api\Service\SerializerService::class . '->clearCache';
    }
);
