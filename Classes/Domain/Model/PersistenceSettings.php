<?php

namespace SourceBroker\T3api\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource as ApiResourceAnnotation;

/**
 * Class Persistence
 */
class PersistenceSettings
{
    /**
     * @var string
     */
    protected $storagePids = '';

    /**
     * @var bool|int
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
        $this->storagePids = (string)($attributes['persistence']['storagePid'] ?? $this->storagePids);
        $this->recursionLevel = (int)($attributes['persistence']['recursive'] ?? $this->recursionLevel);
    }

    /**
     * @return string
     */
    public function getStoragePids(): string
    {
        return $this->storagePids;
    }

    /**
     * @return bool|int
     */
    public function getRecursionLevel()
    {
        return $this->recursionLevel;
    }

}
