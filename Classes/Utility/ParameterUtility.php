<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Utility;

class ParameterUtility
{
    public static function toBoolean(mixed $variable): bool
    {
        return filter_var($variable, FILTER_VALIDATE_BOOLEAN);
    }
}
