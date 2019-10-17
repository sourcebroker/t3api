<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer;

/**
 * @Annotation
 * @Target({"PROPERTY","METHOD"})
 */
class Groups
{
    /**
     * @var array<string>
     * @Required
     */
    public $groups;
}
