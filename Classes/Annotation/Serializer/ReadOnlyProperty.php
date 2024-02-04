<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ReadOnlyProperty
{
    /**
     * @var bool
     */
    public $readOnly = true;
}
