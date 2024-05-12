<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use JMS\Serializer\SerializationContext;
use TYPO3\CMS\Core\Resource\FileInterface;

class FileReferenceService
{
    public function getUrlFromResource(FileInterface $originalResource, SerializationContext $context): ?string
    {
        if (!$originalResource->getPublicUrl()) {
            trigger_error(
                sprintf(
                    'Could not get public URL for file UID:%d. It is probably missing in filesystem.',
                    $originalResource->getUid()
                ),
                E_USER_WARNING
            );
            return null;
        }

        return UrlService::forceAbsoluteUrl(
            $originalResource->getPublicUrl(),
            $context->getAttribute('TYPO3_SITE_URL')
        );
    }
}
