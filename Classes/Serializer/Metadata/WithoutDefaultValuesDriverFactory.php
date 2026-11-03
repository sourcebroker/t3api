<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Metadata;

use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Builder\DriverFactoryInterface;
use Metadata\Driver\DriverInterface;

class WithoutDefaultValuesDriverFactory implements DriverFactoryInterface
{
    public function __construct(private readonly DriverFactoryInterface $driverFactory) {}

    public function createDriver(array $metadataDirs, ?Reader $annotationReader = null): DriverInterface
    {
        return new WithoutDefaultValuesDriver($this->driverFactory->createDriver($metadataDirs, $annotationReader));
    }
}
