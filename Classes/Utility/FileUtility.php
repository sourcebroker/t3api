<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Utility;

use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FileUtility
 */
class FileUtility
{
    /**
     * @param string $path
     *
     * @return string
     */
    public static function createWritableDirectory(string $path): string
    {
        if (!is_dir($path)) {
            try {
                GeneralUtility::mkdir_deep($path);
            } catch (RuntimeException $e) {
                throw new RuntimeException(sprintf('The directory `%s` can not be created.', $path), 1570250836643, $e);
            }
        }

        if (!is_writable($path)) {
            throw new RuntimeException(sprintf('The directory `%s` is not writable.', $path), 1570250839604);
        }

        return $path;
    }
}
