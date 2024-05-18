<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

interface SerializeHandlerInterface
{
    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $object,
        array $type,
        SerializationContext $context
    );
}
