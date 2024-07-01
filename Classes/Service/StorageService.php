<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class StorageService implements SingletonInterface
{
    /**
     * @param int[] $storagePids
     * @return int[]
     */
    public static function getRecursiveStoragePids(array $storagePids, int $recursionDepth = 0): array
    {
        if ($recursionDepth <= 0) {
            return $storagePids;
        }

        $recursiveStoragePids = $storagePids;

        foreach ($storagePids as $startPid) {
            $pids = GeneralUtility::makeInstance(ContentObjectRenderer::class)->getTreeList(
                $startPid,
                $recursionDepth,
            );

            if ($pids !== '') {
                $recursiveStoragePids = array_merge(
                    $recursiveStoragePids,
                    GeneralUtility::intExplode(',', $pids)
                );
            }
        }

        return array_unique($recursiveStoragePids);
    }
}
