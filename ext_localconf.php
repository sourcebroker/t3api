<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][\SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer::ENHANCER_NAME] =
            \SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer::class;

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['serializerHandlers'] = [
            \SourceBroker\Restify\Serializer\Handler\ObjectStorageHandler::class,
            \SourceBroker\Restify\Serializer\Handler\FileReferenceHandler::class,
            \SourceBroker\Restify\Serializer\Handler\ProcessedImageHandler::class,
            \SourceBroker\Restify\Serializer\Handler\RecordUriHandler::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['typesWithAllowedReflectionGetter'] = [
            \SourceBroker\Restify\Serializer\Handler\ProcessedImageHandler::TYPE,
            \SourceBroker\Restify\Serializer\Handler\RecordUriHandler::TYPE,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['collectionResponseClass'] =
            \SourceBroker\Restify\Response\HydraCollectionResponse::class;

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
