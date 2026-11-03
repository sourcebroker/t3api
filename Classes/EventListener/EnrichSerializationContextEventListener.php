<?php

declare(strict_types=1);

namespace SourceBroker\T3api\EventListener;

use SourceBroker\T3api\Event\AfterCreateContextForOperationEvent;
use SourceBroker\T3api\Utility\NormalizedParamsUtility;

class EnrichSerializationContextEventListener
{
    public function __invoke(AfterCreateContextForOperationEvent $createContextForOperationEvent): void
    {
        $normalizedParams = NormalizedParamsUtility::get();
        $port = $normalizedParams->getRequestPort();

        $attributes = [
            'TYPO3_HOST_ONLY' => $normalizedParams->getRequestHostOnly(),
            // string as returned by `GeneralUtility::getIndpEnv()`, empty when the host has no port
            'TYPO3_PORT' => $port > 0 ? (string)$port : '',
            'TYPO3_REQUEST_HOST' => $normalizedParams->getRequestHost(),
            'TYPO3_REQUEST_URL' => $normalizedParams->getRequestUrl(),
            'TYPO3_REQUEST_SCRIPT' => $normalizedParams->getRequestScript(),
            'TYPO3_REQUEST_DIR' => $normalizedParams->getRequestDir(),
            'TYPO3_SITE_URL' => $normalizedParams->getSiteUrl(),
            'TYPO3_SITE_PATH' => $normalizedParams->getSitePath(),
            'TYPO3_SITE_SCRIPT' => $normalizedParams->getSiteScript(),
            'TYPO3_DOCUMENT_ROOT' => $normalizedParams->getDocumentRoot(),
            'TYPO3_SSL' => $normalizedParams->isHttps(),
            'TYPO3_PROXY' => $normalizedParams->isBehindReverseProxy(),
        ];

        foreach ($attributes as $name => $value) {
            $createContextForOperationEvent
                ->getContext()
                ->setAttribute($name, $value);
        }
    }
}
