<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    static function () {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['basePath'] = '_api';
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader'] = 'X-Locale';

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['operationHandlers'] = [
            \SourceBroker\T3api\OperationHandler\OptionsOperationHandler::class => -300,
            \SourceBroker\T3api\OperationHandler\FileUploadOperationHandler::class => -400,
            \SourceBroker\T3api\OperationHandler\CollectionGetOperationHandler::class => -500,
            \SourceBroker\T3api\OperationHandler\CollectionPostOperationHandler::class => -500,
            \SourceBroker\T3api\OperationHandler\CollectionMethodNotAllowedOperationHandler::class => -9999,
            \SourceBroker\T3api\OperationHandler\ItemGetOperationHandler::class => -500,
            \SourceBroker\T3api\OperationHandler\ItemPutOperationHandler::class => -500,
            \SourceBroker\T3api\OperationHandler\ItemPatchOperationHandler::class => -500,
            \SourceBroker\T3api\OperationHandler\ItemDeleteOperationHandler::class => -500,
            \SourceBroker\T3api\OperationHandler\ItemMethodNotAllowedOperationHandler::class => -9999,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerObjectConstructors'] = [
            \SourceBroker\T3api\Serializer\Construction\InitializedObjectConstructor::class,
            \SourceBroker\T3api\Serializer\Construction\ExtbaseObjectConstructor::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerHandlers'] = [
            \SourceBroker\T3api\Serializer\Handler\AbstractDomainObjectHandler::class,
            \SourceBroker\T3api\Serializer\Handler\ObjectStorageHandler::class,
            \SourceBroker\T3api\Serializer\Handler\FileReferenceHandler::class,
            \SourceBroker\T3api\Serializer\Handler\ImageHandler::class,
            \SourceBroker\T3api\Serializer\Handler\RecordUriHandler::class,
            \SourceBroker\T3api\Serializer\Handler\TypolinkHandler::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'] = [
            SourceBroker\T3api\Serializer\Subscriber\GenerateMetadataSubscriber::class,
            SourceBroker\T3api\Serializer\Subscriber\FileReferenceSubscriber::class,
            SourceBroker\T3api\Serializer\Subscriber\AbstractEntitySubscriber::class,
            SourceBroker\T3api\Serializer\Subscriber\ThrowableSubscriber::class,
        ];

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

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['createHashBase']['t3api'] =
            \SourceBroker\T3api\Hook\EnrichHashBase::class . '->init';

        $customizeSerializerContextAttributesSlots = [
            [\SourceBroker\T3api\Slot\AddHydraCollectionResponseSerializationGroup::class, 'execute'],
            [\SourceBroker\T3api\Slot\EnrichSerializationContext::class, 'execute'],
        ];

        foreach ($customizeSerializerContextAttributesSlots as $customizeSerializerContextAttributesSlot) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
                \SourceBroker\T3api\Serializer\ContextBuilder\ContextBuilderInterface::class,
                \SourceBroker\T3api\Serializer\ContextBuilder\ContextBuilderInterface::SIGNAL_CUSTOMIZE_SERIALIZER_CONTEXT_ATTRIBUTES,
                $customizeSerializerContextAttributesSlot[0],
                $customizeSerializerContextAttributesSlot[1]
            );
        }

        if (version_compare(TYPO3_branch, '9.5', '>=')) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][\SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::ENHANCER_NAME] = \SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer::class;
        } else {
            if (
                PHP_SAPI !== 'cli'
                && (
                    $_SERVER['REQUEST_URI'] === ('/' . $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['basePath'])
                    || \TYPO3\CMS\Core\Utility\StringUtility::beginsWith($_SERVER['REQUEST_URI'],
                        '/' . $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['basePath'] . '/')
                )
            ){
                $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader']));
                $languageUid = $_SERVER[$headerKey] ? (int)$_SERVER[$headerKey] : 0;
                \SourceBroker\T3api\Dispatcher\LegacyTypoScriptDispatcher::storeRequest();
                $_SERVER['REQUEST_URI'] = sprintf('/?type=1583185521180&L=%s', $languageUid);
                $_GET['type'] = 1583185521180;
                $_GET['L'] = $languageUid;
                define('IS_T3API_LEGACY_REQUEST', true);
            }

            // since version 9.0.0 registration of loader for doctrine's annotation registry is done in TYPO3 core bootstrap
            /** @var \Composer\Autoload\ClassLoader $loader */
            $loader = require PATH_site . 'vendor/autoload.php';
            \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('inject');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('transient');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('lazy');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('validate');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('cascade');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('ignorevalidation');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('cli');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('flushesCaches');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('uuid');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('identity');
        }
    }
);
