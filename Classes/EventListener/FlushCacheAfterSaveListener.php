<?php

declare(strict_types=1);

namespace SourceBroker\T3api\EventListener;

use SourceBroker\T3api\Event\RecordDeletedEvent;
use SourceBroker\T3api\Event\RecordUpdatedEvent;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

final class FlushCacheAfterSaveListener
{
    public function __construct(private readonly FrontendInterface $cache) {}

    public function afterUpdated(RecordUpdatedEvent $event): void
    {
        $this->cache->flushByTags($this->resolveUpdateTags($event));
    }

    /**
     * A deleted record's own `<table>_<uid>` tag is implied dead along with it, and the
     * `<table>--collection` tag covers every collection that contained the record - the same
     * two-tag shape a membership-changing update flushes, see `resolveUpdateTags()`.
     */
    public function afterDeleted(RecordDeletedEvent $event): void
    {
        $this->cache->flushByTags([$event->getTable() . '--collection', $event->getTable() . '_' . $event->getUid()]);
    }

    /**
     * A plain update only needs the record's own `<table>_<uid>` tag flushed - every cached
     * collection that already contains the record was tagged with that same uid when it was
     * serialized, so it still refreshes correctly. A membership-changing update flushes both the
     * `<table>--collection` tag - covering other collections whose membership may now include or
     * exclude the record - and the record's own `<table>_<uid>` tag, since the record's own
     * previously-cached item/collection-member entries may now be stale too (e.g. a newly-hidden
     * record's cached item response must stop being served). Including the uid tag is always safe
     * even when nothing was ever cached under it yet, so create/move/undelete need no special case.
     *
     * @return string[]
     */
    private function resolveUpdateTags(RecordUpdatedEvent $event): array
    {
        $uidTag = $event->getTable() . '_' . $event->getUid();
        if (!$event->changesCollectionMembership()) {
            return [$uidTag];
        }

        return [$event->getTable() . '--collection', $uidTag];
    }
}
