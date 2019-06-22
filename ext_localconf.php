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
    }
);
