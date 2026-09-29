<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

/**
 * Thrown when query-modifier filters are applied on a repository that was not built for
 * a collection operation (see CommonRepository::findFiltered()).
 */
final class MissingCollectionOperationException extends \RuntimeException {}
