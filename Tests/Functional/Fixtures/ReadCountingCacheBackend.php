<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Fixtures;

use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;

/**
 * Counts reads of cache entries, e.g. to check how often one request reads the API resources.
 */
final class ReadCountingCacheBackend extends TransientMemoryBackend
{
    public int $readCount = 0;

    /**
     * @param string $entryIdentifier
     */
    public function get($entryIdentifier): mixed
    {
        $this->readCount++;

        return parent::get($entryIdentifier);
    }
}
