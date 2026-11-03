<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Serializer\Metadata;

use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\Driver\DriverInterface;
use Metadata\PropertyMetadata as BasePropertyMetadata;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Serializer\Metadata\WithoutDefaultValuesDriver;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class WithoutDefaultValuesDriverTest extends UnitTestCase
{
    public string $title = 'Default title';

    #[Test]
    public function defaultValuesOfPropertiesAreRemoved(): void
    {
        $propertyMetadata = new PropertyMetadata(self::class, 'title');
        $propertyMetadata->hasDefault = true;
        $propertyMetadata->defaultValue = 'Default title';
        $classMetadata = new ClassMetadata(self::class);
        $classMetadata->addPropertyMetadata($propertyMetadata);

        $driver = self::createStub(DriverInterface::class);
        $driver->method('loadMetadataForClass')->willReturn($classMetadata);

        $result = (new WithoutDefaultValuesDriver($driver))->loadMetadataForClass(new \ReflectionClass(self::class));

        self::assertSame($classMetadata, $result);
        self::assertFalse($propertyMetadata->hasDefault);
        self::assertNull($propertyMetadata->defaultValue);
    }

    /**
     * Older versions of JMS serializer have no `hasDefault` - no dynamic property may be created.
     */
    #[Test]
    public function propertyMetadataWithoutDefaultValueSupportIsNotChanged(): void
    {
        $propertyMetadata = new BasePropertyMetadata(self::class, 'title');
        $classMetadata = new ClassMetadata(self::class);
        $classMetadata->addPropertyMetadata($propertyMetadata);

        $driver = self::createStub(DriverInterface::class);
        $driver->method('loadMetadataForClass')->willReturn($classMetadata);

        (new WithoutDefaultValuesDriver($driver))->loadMetadataForClass(new \ReflectionClass(self::class));

        self::assertFalse(property_exists($propertyMetadata, 'hasDefault'));
    }

    #[Test]
    public function missingClassMetadataIsReturnedUnchanged(): void
    {
        $driver = self::createStub(DriverInterface::class);
        $driver->method('loadMetadataForClass')->willReturn(null);

        self::assertNull((new WithoutDefaultValuesDriver($driver))->loadMetadataForClass(new \ReflectionClass(self::class)));
    }
}
