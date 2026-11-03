<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Metadata;

use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\ClassMetadata;
use Metadata\Driver\DriverInterface;

/**
 * Removes default values of properties added by `DefaultValuePropertyDriver` of JMS serializer. With default values
 * JMS sets the default value of every property missing in the request body, also when an existing object is updated
 * (PATCH, cascade update of a related record) - all properties which were not sent would be reset. New objects get
 * their default values from the constructor, which is called by object constructors of t3api.
 *
 * Default values are set since JMS serializer 3.18.0. This driver can be removed when JMS serializer does not set them
 * for existing objects anymore.
 *
 * @see https://github.com/schmittjoh/serializer/issues/1610
 */
class WithoutDefaultValuesDriver implements DriverInterface
{
    public function __construct(private readonly DriverInterface $driver) {}

    public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata
    {
        $classMetadata = $this->driver->loadMetadataForClass($class);

        foreach ($classMetadata?->propertyMetadata ?? [] as $propertyMetadata) {
            // `hasDefault` does not exist in older versions of JMS serializer
            if ($propertyMetadata instanceof PropertyMetadata && property_exists($propertyMetadata, 'hasDefault')) {
                $propertyMetadata->hasDefault = false;
                $propertyMetadata->defaultValue = null;
            }
        }

        return $classMetadata;
    }
}
