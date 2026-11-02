<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

/**
 * Returned to the client instead of an unexpected exception outside of `Development` context, so the
 * response does not expose its message (paths, database details, ...). The event ID identifies the
 * log entry with the original exception.
 */
class InternalServerErrorException extends AbstractException
{
    public function __construct(string $eventId)
    {
        $this->title = self::translate('exception.internal_server_error.title');
        parent::__construct(
            self::translate('exception.internal_server_error.description', [$eventId]),
            1793285051
        );
    }
}
