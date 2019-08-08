<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

/**
 * Interface DeserializeHandlerInterface
 *
 * @todo Deserialization is not working yet. This interface is created only to keep architecture for future
 *     implementation
 */
interface DeserializeHandlerInterface
{
    /**
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $data
     * @param array $type
     * @param DeserializationContext $context
     *
     * @return mixed
     */
    public function deserialize(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context
    );
}
