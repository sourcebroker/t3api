<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Domain\Model;

/**
 * Class AbstractOperationResourceSettings
 */
abstract class AbstractOperationResourceSettings
{
    /**
     * @param $attributes
     * @param static|null $base
     * @return static
     */
    public static function create(array $attributes = [], ?self $base = null): self
    {
        return $base ? clone $base : new static();
    }
}
