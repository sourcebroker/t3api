<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Utility;

/**
 * Class ParameterUtility
 */
class ParameterUtility
{
    /**
     * @param mixed $variable
     *
     * @return bool
     */
    public static function toBoolean($variable): bool
    {
        return filter_var($variable, FILTER_VALIDATE_BOOLEAN);
    }
}
