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
    protected string $folder = '1:/user_upload/';

    /**
     * @var string[]
     */
    protected array $allowedFileExtensions = [];

    protected string $conflictMode = DuplicationBehavior::RENAME;

    protected string $filenameHashAlgorithm = 'md5';

    protected string $contentHashAlgorithm = 'md5';

    protected string $filenameMask = '[filename][extensionWithDot]';

    /**
     * @param UploadSettings|null $uploadSettings
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
        $uploadSettings->filenameHashAlgorithm = $attributes['filenameHashAlgorithm'] ?? $uploadSettings->filenameHashAlgorithm;
        $uploadSettings->contentHashAlgorithm = $attributes['contentHashAlgorithm'] ?? $uploadSettings->contentHashAlgorithm;
        $uploadSettings->filenameMask = $attributes['filenameMask'] ?? $uploadSettings->filenameMask;

        return $uploadSettings;
    }

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

    public function getConflictMode(): string
    {
        return $this->conflictMode;
    }

    public function getFilenameHashAlgorithm(): string
    {
        return $this->filenameHashAlgorithm;
    }

    public function getContentHashAlgorithm(): string
    {
        return $this->contentHashAlgorithm;
    }

    public function getFilenameMask(): string
    {
        return $this->filenameMask;
    }
}
