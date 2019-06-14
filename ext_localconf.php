<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][\SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer::ENHANCER_NAME] =
            \SourceBroker\Restify\Routing\Enhancer\ResourceEnhancer::class;

        // @todo add transformers prioritization?
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['transformers'] = [
            [
                'type' => \TYPO3\CMS\Extbase\Persistence\ObjectStorage::class,
                'class' => \SourceBroker\Restify\Transformer\ObjectStorageTransformer::class,
            ],
            [
                'type' => \TYPO3\CMS\Extbase\Domain\Model\FileReference::class,
                'class' => \SourceBroker\Restify\Transformer\FileReferenceTransformer::class,
            ],
        ];
    }
);
