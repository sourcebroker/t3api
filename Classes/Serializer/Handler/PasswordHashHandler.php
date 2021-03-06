<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;

class PasswordHashHandler extends AbstractHandler implements SerializeHandlerInterface, DeserializeHandlerInterface
{
    public const TYPE = 'PasswordHash';

    protected static $supportedTypes = [self::TYPE];

    /**
     * @var PasswordHashFactory
     */
    private $passwordHashFactory;

    public function injectPasswordHashFactory(PasswordHashFactory $passwordHashFactory): void
    {
        $this->passwordHashFactory = $passwordHashFactory;
    }

    // serialize method has to exists to handle `PasswordHash` type and avoid error "Class PasswordHash does not exist"
    public function serialize(
        SerializationVisitorInterface $visitor,
        $object,
        array $type,
        SerializationContext $context
    ) {
        return $object;
    }

    public function deserialize(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context
    ): ?string {
        if (!is_string($data)) {
            return null;
        }

        return $this->passwordHashFactory->getDefaultHashInstance('FE')->getHashedPassword($data);
    }
}
