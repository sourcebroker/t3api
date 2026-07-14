<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use SourceBroker\T3api\Attribute\AsSerializerHandler;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

#[AsSerializerHandler]
class CurrentFeUserHandler extends AbstractHandler implements DeserializeHandlerInterface
{
    /**
     * @var string
     */
    public const TYPE = 'CurrentFeUser';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    public function __construct(protected readonly PersistenceManager $persistenceManager) {}

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function deserialize(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context
    ): AbstractDomainObject {
        return $this->persistenceManager->getObjectByIdentifier($data, $type['params'][0]);
    }
}
