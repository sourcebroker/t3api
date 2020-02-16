<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Model;

use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UploadSettings
 */
class UploadSettings extends AbstractOperationResourceSettings
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
     * @param array $attributes
     * @param UploadSettings $uploadSettings
     * @return UploadSettings
     */
    public static function create(
        array $attributes = [],
        ?AbstractOperationResourceSettings $uploadSettings = null
    ): AbstractOperationResourceSettings {
        $uploadSettings = parent::create($attributes, $uploadSettings);
        $uploadSettings->folder = $attributes['folder'] ?? $uploadSettings->folder;
        $uploadSettings->allowedFileExtensions = $attributes['allowedFileExtensions'] ??
            GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
        $uploadSettings->conflictMode = $attributes['conflictMode'] ?? $uploadSettings->conflictMode;

        return $uploadSettings;
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
