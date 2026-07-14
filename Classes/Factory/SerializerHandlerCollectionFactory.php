<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Factory;

use SourceBroker\T3api\Configuration\Configuration;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class SerializerHandlerCollectionFactory
{
    use GetMergedTrait;

    public function __construct(
        #[AutowireIterator('t3api.serializer_handler')]
        protected readonly \Traversable $serializerHandlers
    ) {}

    public function get(): array
    {
        return $this->getInstances($this->serializerHandlers, Configuration::getSerializerHandlers());
    }
}
