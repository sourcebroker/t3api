<?php

use SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer;
use SourceBroker\Restify\Serializer\Handler as Handler;
use SourceBroker\Restify\Serializer\Subscriber as Subscriber;
use SourceBroker\Restify\Response\HydraCollectionResponse;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][ResourceEnhancer::ENHANCER_NAME] = ResourceEnhancer::class;

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['serializerHandlers'] = [
            Handler\ObjectStorageHandler::class,
            Handler\FileReferenceHandler::class,
            Handler\ProcessedImageHandler::class,
            Handler\RecordUriHandler::class,
            Handler\TypolinkHandler::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['typesWithAllowedReflectionGetter'] = [
            Handler\ProcessedImageHandler::TYPE,
            Handler\RecordUriHandler::TYPE,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['serializerSubscribers'] = [
            Subscriber\AbstractEntitySubscriber::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['forceEntityProperties'] = [
            'uid',
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['collectionResponseClass'] = HydraCollectionResponse::class;

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['pagination'] = [
            'pagination_enabled' => true,
            'pagination_client_enabled' => false,
            'pagination_items_per_page' => 30,
            'maximum_items_per_page' => 9999999,
            'pagination_client_items_per_page' => false,
            'items_per_page_parameter_name' => 'itemsPerPage',
            'enabled_parameter_name' => 'pagination',
            'page_parameter_name' => 'page',
        ];
    }
);
