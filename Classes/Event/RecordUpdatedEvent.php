<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Event;

final class RecordUpdatedEvent extends AbstractRecordEvent
{
    public function __construct(
        int $uid,
        string $table,
        private readonly bool $changesCollectionMembership = false
    ) {
        parent::__construct($uid, $table);
    }

    /**
     * True when the change may alter which collections contain the record (new, moved or
     * undeleted) - the listener flushes the whole `<table>` tag for these. False for a plain
     * field update to an already-persisted record, which only needs its own `<table>_<uid>`
     * tag flushed, since collections that already contain the record carry that tag too.
     */
    public function changesCollectionMembership(): bool
    {
        return $this->changesCollectionMembership;
    }
}
