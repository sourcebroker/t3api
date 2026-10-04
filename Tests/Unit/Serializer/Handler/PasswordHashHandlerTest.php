<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Annotation\Serializer\Type\PasswordHash;
use SourceBroker\T3api\Serializer\Handler\PasswordHashHandler;
use TYPO3\CMS\Core\Crypto\PasswordHashing\BcryptPasswordHash;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Real `PasswordHashFactory` is used - it is a `readonly` class since TYPO3 13 and PHPUnit versions
 * used with lowest dependencies can not create test doubles of readonly classes.
 */
class PasswordHashHandlerTest extends UnitTestCase
{
    private array $typo3ConfVarsBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->typo3ConfVarsBackup = $GLOBALS['TYPO3_CONF_VARS'] ?? [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['availablePasswordHashAlgorithms'] = [BcryptPasswordHash::class];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['passwordHashing'] = [
            'className' => BcryptPasswordHash::class,
            'options' => [],
        ];
    }

    protected function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS'] = $this->typo3ConfVarsBackup;
        parent::tearDown();
    }

    #[Test]
    public function serializationReturnsNull(): void
    {
        $handler = new PasswordHashHandler(new PasswordHashFactory());

        self::assertNull($handler->serialize(
            self::createStub(SerializationVisitorInterface::class),
            '$argon2i$v=19$m=65536,t=16,p=1$c29tZXNhbHQ$aGFzaA',
            ['name' => PasswordHashHandler::TYPE, 'params' => []],
            SerializationContext::create()
        ));
    }

    #[Test]
    public function serializationReturnsStoredValueWhenEnabledWithParameter(): void
    {
        $handler = new PasswordHashHandler(new PasswordHashFactory());

        self::assertSame('$argon2i$stored', $handler->serialize(
            self::createStub(SerializationVisitorInterface::class),
            '$argon2i$stored',
            ['name' => PasswordHashHandler::TYPE, 'params' => [PasswordHashHandler::PARAM_SERIALIZE]],
            SerializationContext::create()
        ));
    }

    #[Test]
    public function annotationPassesSerializeParameterOnlyWhenEnabled(): void
    {
        $annotation = new PasswordHash();
        self::assertSame([], $annotation->getParams());

        $annotation->serialize = true;
        self::assertSame([PasswordHashHandler::PARAM_SERIALIZE], $annotation->getParams());
    }

    #[Test]
    public function deserializationHashesPassword(): void
    {
        $hash = $this->deserialize('secret');

        self::assertIsString($hash);
        self::assertNotSame('secret', $hash);
        self::assertTrue((new BcryptPasswordHash())->checkPassword('secret', $hash));
    }

    #[Test]
    public function deserializationReturnsNullForNonStringValue(): void
    {
        self::assertNull($this->deserialize(['secret']));
    }

    private function deserialize(mixed $data): ?string
    {
        return (new PasswordHashHandler(new PasswordHashFactory()))->deserialize(
            self::createStub(DeserializationVisitorInterface::class),
            $data,
            ['name' => PasswordHashHandler::TYPE, 'params' => []],
            DeserializationContext::create()
        );
    }
}
