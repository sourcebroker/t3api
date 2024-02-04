<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Processor;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

interface ProcessorInterface
{
    public function process(Request $request, ResponseInterface $response): void;
}
