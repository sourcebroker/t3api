<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UploadSettings
 */
class UploadSettings
{
    /**
     * @var string
     */
    protected $folder = '1:/user_upload/';

    /**
     * @var string[]
     */
    protected $allowedFileExtensions;

    /**
     * @var string
     */
    protected $conflictMode = DuplicationBehavior::RENAME;

    /**
     * ClientSidePagination constructor.
     *
     * @param ApiResourceAnnotation $apiResource
     */
    public function __construct(ApiResourceAnnotation $apiResource)
    {
        $attributes = $apiResource->getAttributes();
        $this->folder = $attributes['upload']['folder'] ?? $this->folder;
        $this->allowedFileExtensions = $attributes['upload']['allowedFileExtensions'] ??
            GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
        $this->conflictMode = $attributes['upload']['conflictMode'] ?? $this->conflictMode;
    }

    /**
     * @return string
     */
    public function getFolder(): string
    {
        return $this->folder;
    }

    /**
     * @return string[]
     */
    public function getAllowedFileExtensions(): array
    {
        return $this->allowedFileExtensions;
    }

    /**
     * @return string
     */
    public function getConflictMode(): string
    {
        return $this->conflictMode;
    }
}
