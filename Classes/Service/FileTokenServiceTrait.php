<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

trait FileTokenServiceTrait
{
    protected ?FileTokenService $fileTokenService = null;

    /**
     * Injected by method (not constructor) so that adding new dependency does not break classes which extend this
     * class and override the constructor.
     */
    public function injectFileTokenService(FileTokenService $fileTokenService): void
    {
        $this->fileTokenService = $fileTokenService;
    }

    protected function getFileTokenService(): FileTokenService
    {
        return $this->fileTokenService ??= GeneralUtility::makeInstance(FileTokenService::class);
    }
}
