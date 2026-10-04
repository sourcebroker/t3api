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
    /**
     * @var string
     */
    public const TYPE = 'PasswordHash';

    /**
     * Handler parameter enabling serialization of stored password hash (`PasswordHash<'serialize'>`)
     */
    public const PARAM_SERIALIZE = 'serialize';

    protected static $supportedTypes = [self::TYPE];

    public function __construct(private readonly PasswordHashFactory $passwordHashFactory) {}

    /**
     * The type is meant for deserialization (hashing of password sent in payload). Stored password hash
     * is returned on serialization only if enabled with parameter `serialize`, otherwise `null`.
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $object,
        array $type,
        SerializationContext $context
    ): mixed {
        return in_array(self::PARAM_SERIALIZE, $this->getDecodedParams($type['params'] ?? []), true) ? $object : null;
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
