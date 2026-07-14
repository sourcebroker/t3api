<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Event;

abstract class AbstractRecordEvent
{
    public function __construct(
        protected readonly int $uid,
        protected readonly string $table
    ) {}

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
