<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use SourceBroker\T3api\Service\InlineRelationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * For handlers which assign existing records given by UID.
 */
trait InlineRelationServiceTrait
{
    protected ?InlineRelationService $inlineRelationService = null;

    /**
     * Injected by method (not constructor) so that adding new dependency does not break handlers which extend this
     * class and override the constructor.
     */
    public function injectInlineRelationService(InlineRelationService $inlineRelationService): void
    {
        $this->inlineRelationService = $inlineRelationService;
    }

    protected function getInlineRelationService(): InlineRelationService
    {
        return $this->inlineRelationService ??= GeneralUtility::makeInstance(InlineRelationService::class);
    }
}
