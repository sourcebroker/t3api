<?php

declare(strict_types=1);

namespace SourceBroker\T3api\EventListener;

use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EnrichSerializationContextEventListener
{
    public function __invoke(AfterCreateContextForOperationEvent $createContextForOperationEvent): void
    {
        $attributes = [
            'TYPO3_HOST_ONLY' => GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'),
            'TYPO3_PORT' => GeneralUtility::getIndpEnv('TYPO3_PORT'),
            'TYPO3_REQUEST_HOST' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'),
            'TYPO3_REQUEST_URL' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            'TYPO3_REQUEST_SCRIPT' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_SCRIPT'),
            'TYPO3_REQUEST_DIR' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR'),
            'TYPO3_SITE_URL' => GeneralUtility::getIndpEnv('TYPO3_SITE_URL'),
            'TYPO3_SITE_PATH' => GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'),
            'TYPO3_SITE_SCRIPT' => GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT'),
            'TYPO3_DOCUMENT_ROOT' => GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT'),
            'TYPO3_SSL' => GeneralUtility::getIndpEnv('TYPO3_SSL'),
            'TYPO3_PROXY' => GeneralUtility::getIndpEnv('TYPO3_PROXY'),
        ];

        foreach ($attributes as $name => $value) {
            $createContextForOperationEvent
                ->getContext()
                ->setAttribute($name, $value);
        }
    }
}
