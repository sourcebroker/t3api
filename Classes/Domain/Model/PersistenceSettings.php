<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class PersistenceSettings extends AbstractOperationResourceSettings
{
    /**
     * @var int[]
     */
    protected array $storagePids = [];

    protected int $recursionLevel = 0;

    public static function create(
        array $attributes = [],
        ?AbstractOperationResourceSettings $persistenceSettings = null
    ): AbstractOperationResourceSettings {
        $persistenceSettings = parent::create($attributes, $persistenceSettings);
        if (!empty($attributes['storagePid'])) {
            $persistenceSettings->storagePids = GeneralUtility::intExplode(',', $attributes['storagePid']);
        }
        $persistenceSettings->recursionLevel = (int)($attributes['recursive'] ?? $persistenceSettings->recursionLevel);

        return $persistenceSettings;
    }

    /**
     * @return int[]
     */
    public function getStoragePids(): array
    {
        return $this->storagePids;
    }

    public function getRecursionLevel(): int
    {
        return $this->recursionLevel;
    }

    public function getMainStoragePid(): int
    {
        if (empty($this->storagePids)) {
            return 0;
        }

        return $this->storagePids[0];
    }
}
