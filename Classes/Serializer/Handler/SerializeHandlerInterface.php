<?php
declare(strict_types=1);

namespace SourceBroker\T3Api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Interface SerializeHandlerInterface
 */
interface SerializeHandlerInterface
{
    /**
     * @param SerializationVisitorInterface $visitor
     * @param ObjectStorage $objectStorage
     * @param array $type
     * @param SerializationContext $context
     *
     * @return array
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $objectStorage,
        array $type,
        SerializationContext $context
    );
}
