<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Persistence
 */
class PersistenceSettings
{
    /**
     * @var int[]
     */
    protected $storagePids = [];

    /**
     * @var int
     */
    protected $recursionLevel = 0;

    /**
     * ClientSidePagination constructor.
     *
     * @param ApiResourceAnnotation $apiResource
     */
    public function __construct(ApiResourceAnnotation $apiResource)
    {
        $attributes = $apiResource->getAttributes();
        if (!empty($attributes['persistence']['storagePid'])) {
            $this->storagePids = GeneralUtility::intExplode(',', $attributes['persistence']['storagePid']);
        }
        $this->recursionLevel = (int)($attributes['persistence']['recursive'] ?? $this->recursionLevel);
    }

    /**
     * @return int[]
     */
    public function getStoragePids(): array
    {
        return $this->storagePids;
    }

    /**
     * @return int
     */
    public function getRecursionLevel()
    {
        return $this->recursionLevel;
    }

    /**
     * @return int|null
     */
    public function getMainStoragePid(): int
    {
        if (empty($this->storagePids)) {
            return 0;
        }

        return $this->storagePids[0];
    }
}
