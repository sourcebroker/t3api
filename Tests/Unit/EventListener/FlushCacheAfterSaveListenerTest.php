<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\EventListener;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Event\RecordDeletedEvent;
use SourceBroker\T3api\Event\RecordUpdatedEvent;
use SourceBroker\T3api\EventListener\FlushCacheAfterSaveListener;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class FlushCacheAfterSaveListenerTest extends UnitTestCase
{
    #[Test]
    public function flushesOnlyRecordTagOnPlainUpdate(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())->method('flushByTags')->with(['tx_foo_5']);

        (new FlushCacheAfterSaveListener($cache))->afterUpdated(new RecordUpdatedEvent(5, 'tx_foo'));
    }

    #[Test]
    public function flushesCollectionAndRecordTagsWhenCollectionMembershipChanges(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())->method('flushByTags')->with(['tx_foo--collection', 'tx_foo_5']);

        (new FlushCacheAfterSaveListener($cache))->afterUpdated(
            new RecordUpdatedEvent(5, 'tx_foo', changesCollectionMembership: true)
        );
    }

    #[Test]
    public function flushesCollectionAndRecordTagsOnDelete(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())->method('flushByTags')->with(['tx_foo--collection', 'tx_foo_7']);

        (new FlushCacheAfterSaveListener($cache))->afterDeleted(new RecordDeletedEvent(7, 'tx_foo'));
    }
}
