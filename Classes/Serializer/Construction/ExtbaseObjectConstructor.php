<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Construction;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtbaseObjectConstructor implements ObjectConstructorInterface
{
    /**
     * {@inheritdoc}
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        return GeneralUtility::makeInstance($metadata->name);
    }
}
