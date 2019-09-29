<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;

/**
 * Class StorageService
 */
class StorageService implements SingletonInterface
{
    /**
     * @var FrontendConfigurationManager
     */
    protected $frontendConfigurationManager;

    /**
     * @param FrontendConfigurationManager $frontendConfigurationManager
     */
    public function injectFrontendConfigurationManager(FrontendConfigurationManager $frontendConfigurationManager)
    {
        $this->frontendConfigurationManager = $frontendConfigurationManager;
    }

    /**
     * @param int[] $storagePids
     * @param int $recursionDepth
     *
     * @return int[]
     */
    public function getRecursiveStoragePids(array $storagePids, $recursionDepth = 0): array
    {
        if ($recursionDepth <= 0) {
            return $storagePids;
        }

        $recursiveStoragePids = [];

        foreach ($storagePids as $startPid) {
            $pids = $this->frontendConfigurationManager->getContentObject()->getTreeList($startPid, $recursionDepth, 0);

            if (!empty($pids)) {
                $recursiveStoragePids = array_merge(
                    $recursiveStoragePids,
                    GeneralUtility::intExplode(',', $pids)
                );
            }
        }

        return array_unique($recursiveStoragePids);
    }

}
