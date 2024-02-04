<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\ContextBuilder;

use JMS\Serializer\Context;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;

interface ContextBuilderInterface
{
    public function create(): Context;
    public function createFromOperation(OperationInterface $operation, Request $request): Context;
}
