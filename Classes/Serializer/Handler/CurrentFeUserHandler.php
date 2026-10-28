<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
    ): ?AbstractDomainObject {
        $frontendUserId = (int)GeneralUtility::makeInstance(Context::class)
            ->getPropertyFromAspect('frontend.user', 'id', 0);

        if ($frontendUserId <= 0) {
            return null;
        }

        return $this->persistenceManager->getObjectByIdentifier($frontendUserId, $type['params'][0]);
    }
}
