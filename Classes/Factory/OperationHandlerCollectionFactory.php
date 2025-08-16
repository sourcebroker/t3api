<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Factory;

use SourceBroker\T3api\Configuration\Configuration;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class OperationHandlerCollectionFactory
{
    use GetMergedTrait;

    public function __construct(
        #[AutowireIterator('t3api.operation_handler')]
        private readonly \Traversable $operationHandlers,
    ) {}

    public function get(): array
    {
        return $this->getInstances($this->operationHandlers, Configuration::getOperationHandlers());
    }
}
