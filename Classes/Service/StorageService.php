<?php

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
     * @param string|int $storagePid
     * @param int $recursionDepth
     *
     * @return int[]
     */
    public function getRecursiveStoragePids($storagePid, $recursionDepth = 0): array
    {
        if ($recursionDepth <= 0) {
            return GeneralUtility::intExplode(',', $storagePid);
        }
        $recursiveStoragePids = '';
        $storagePids = GeneralUtility::intExplode(',', $storagePid);
        foreach ($storagePids as $startPid) {
            $pids = $this->frontendConfigurationManager->getContentObject()->getTreeList($startPid, $recursionDepth, 0);
            if ((string)$pids !== '') {
                $recursiveStoragePids .= $pids . ',';
            }
        }

        return GeneralUtility::intExplode(',', $recursiveStoragePids);
    }

}
