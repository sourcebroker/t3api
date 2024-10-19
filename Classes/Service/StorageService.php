<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $pageRepository = GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Domain\Repository\PageRepository::class,
            $context
        );
        $recursiveStoragePids = [];

        foreach ($storagePids as $startPid) {
            $pids = $pageRepository->getDescendantPageIdsRecursive(
                $startPid,
                $recursionDepth,
            );

            if ($pids !== '') {
                $recursiveStoragePids[] = $pids;
            }
        }

        return array_unique(array_merge($storagePids, ...$recursiveStoragePids));
    }
}
