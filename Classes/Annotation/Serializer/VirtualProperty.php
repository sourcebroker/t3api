<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class VirtualProperty
{
    /**
     * @var string
     */
    public $name;
}
