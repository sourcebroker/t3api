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
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PasswordHashHandlerTest extends UnitTestCase
{
    #[Test]
    public function serializationReturnsNull(): void
    {
        $handler = new PasswordHashHandler(self::createStub(PasswordHashFactory::class));

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
        $handler = new PasswordHashHandler(self::createStub(PasswordHashFactory::class));

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
        $passwordHash = self::createStub(PasswordHashInterface::class);
        $passwordHash->method('getHashedPassword')->willReturnCallback(static fn(string $password): string => 'hashed:' . $password);
        $passwordHashFactory = self::createStub(PasswordHashFactory::class);
        $passwordHashFactory->method('getDefaultHashInstance')->willReturn($passwordHash);

        self::assertSame('hashed:secret', $this->deserialize($passwordHashFactory, 'secret'));
    }

    #[Test]
    public function deserializationReturnsNullForNonStringValue(): void
    {
        self::assertNull($this->deserialize(self::createStub(PasswordHashFactory::class), ['secret']));
    }

    private function deserialize(PasswordHashFactory $passwordHashFactory, mixed $data): ?string
    {
        return (new PasswordHashHandler($passwordHashFactory))->deserialize(
            self::createStub(DeserializationVisitorInterface::class),
            $data,
            ['name' => PasswordHashHandler::TYPE, 'params' => []],
            DeserializationContext::create()
        );
    }
}
