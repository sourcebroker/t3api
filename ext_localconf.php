<?php

use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use SourceBroker\T3api\Serializer\Handler as Handler;
use SourceBroker\T3api\Serializer\Subscriber as Subscriber;
use SourceBroker\T3api\Response\HydraCollectionResponse;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use SourceBroker\T3api\Response\MainEndpointResponse;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][ResourceEnhancer::ENHANCER_NAME] = ResourceEnhancer::class;

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerHandlers'] = [
            Handler\ObjectStorageHandler::class,
            Handler\FileReferenceHandler::class,
            Handler\ProcessedImageHandler::class,
            Handler\RecordUriHandler::class,
            Handler\TypolinkHandler::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['typesWithAllowedReflectionGetter'] = [
            Handler\ProcessedImageHandler::TYPE,
            Handler\RecordUriHandler::TYPE,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'] = [
            Subscriber\AbstractEntitySubscriber::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['forceEntityProperties'] = [
            'uid',
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'] = HydraCollectionResponse::class;

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass'] = MainEndpointResponse::class;

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
                'frontend' => VariableFrontend::class,
                'backend' => SimpleFileBackend::class,
                'options' => [
                    'defaultLifetime' => 0,
                ],
                'groups' => ['system'],
            ];
        }
    }
);
