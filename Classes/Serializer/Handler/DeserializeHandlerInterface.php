<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

interface DeserializeHandlerInterface
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function deserialize(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context
    );
}
