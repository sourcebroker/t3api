<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Factory;

use SourceBroker\T3api\Configuration\Configuration;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ProcessorsCollectionFactory
{
    use GetMergedTrait;

    public function __construct(
        #[AutowireIterator('t3api.processor')]
        private readonly \Traversable $processors,
    ) {}

    public function get(): array
    {
        return $this->getInstances($this->processors, Configuration::getProcessors());
    }
}
