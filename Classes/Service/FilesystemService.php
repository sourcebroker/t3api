<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\Service\OpcodeCacheService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class FilesystemService
{
    public function flushDirectory($directory, $keepOriginalDirectory = false, $flushOpcodeCache = false)
    {
        $result = false;

        if (is_link($directory)) {
            // Avoid attempting to rename the symlink see #87367
            $directory = realpath($directory);
        }

        if (is_dir($directory)) {
            $temporaryDirectory = rtrim($directory, '/') . '.' . StringUtility::getUniqueId('remove');
            if (rename($directory, $temporaryDirectory)) {
                if ($flushOpcodeCache) {
                    GeneralUtility::makeInstance(OpcodeCacheService::class)->clearAllActive($directory);
                }
                if ($keepOriginalDirectory) {
                    GeneralUtility::mkdir($directory);
                }
                clearstatcache();
                $result = GeneralUtility::rmdir($temporaryDirectory, true);
            }
        }

        return $result;
    }
}
