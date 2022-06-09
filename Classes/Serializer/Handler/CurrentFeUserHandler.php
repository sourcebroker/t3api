<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class CurrentFeUserHandler extends AbstractHandler implements DeserializeHandlerInterface
{
    public const TYPE = 'CurrentFeUser';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

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
