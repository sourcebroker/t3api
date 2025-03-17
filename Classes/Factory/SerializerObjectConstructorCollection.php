<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Factory;

use SourceBroker\T3api\Configuration\Configuration;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class SerializerObjectConstructorCollection
{
    use GetMergedTrait;

    public function __construct(
        #[AutowireIterator('t3api.serializer_object_constructor')]
        protected readonly \Traversable $constructorsInstances,
    ) {}

    public function get(): array
    {
        return $this->getInstances($this->constructorsInstances, Configuration::getSerializerObjectConstructors());
    }
}
