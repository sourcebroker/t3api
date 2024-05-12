<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Utility;

use SourceBroker\T3api\Utility\FileUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class FileUtilityTest
 */
class FileUtilityTest extends UnitTestCase
{
    /**
     * @test
     */
    public function createWritableDirectoryCreatesNewWritableDirectory()
    {
        $directoryPath = FileUtility::createWritableDirectory($this->getPathToNotExistingDirectory(sys_get_temp_dir()));

        self::assertDirectoryExists($directoryPath);
        self::assertDirectoryIsWritable($directoryPath);
    }

    /**
     * @test
     */
    public function createWritableDirectoryThrowsExceptionIfCanNotCreate()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1570250836643);

        FileUtility::createWritableDirectory($this->getPathToNotExistingDirectory('/root'));
    }

    /**
     * @test
     */
    public function createWritableDirectoryThrowsExceptionIfNotWritable()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1570250839604);

        FileUtility::createWritableDirectory($this->getPathToNotWritableDirectory());
    }

    /**
     * @param string $rootPath
     *
     * @return string
     */
    protected function getPathToNotExistingDirectory(string $rootPath = ''): string
    {
        do {
            $directoryPath = rtrim($rootPath, '/') . '/TestWritableDir' . md5(time() . microtime(true));
        } while (is_dir($directoryPath));

        return $directoryPath;
    }

    /**
     * Returns path to random not writable directory
     *
     * @param string $rootPath
     *
     * @return string
     */
    protected function getPathToNotWritableDirectory(string $rootPath = '/'): string
    {
        $dirs = glob(rtrim($rootPath, '/') . '/*', GLOB_ONLYDIR);
        $dirs = $dirs === false ? [] : $dirs;

        shuffle($dirs);

        foreach ($dirs as $dir) {
            if (!is_writable($dir)) {
                return $dir;
            }
            if (!is_writable($childDir = $this->getPathToNotWritableDirectory($rootPath))) {
                return $childDir;
            }
        }

        return '/';
    }
}
