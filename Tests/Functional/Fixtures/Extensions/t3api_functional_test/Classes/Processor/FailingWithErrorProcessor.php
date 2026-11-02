<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Processor;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Throws an `Error` (not an `Exception`), like a custom operation handler returning a wrong type.
 */
class FailingWithErrorProcessor implements ProcessorInterface
{
    public const MESSAGE = 'Return value must be of type string, array returned';

    public function process(Request $request, ResponseInterface &$response): void
    {
        throw new \TypeError(self::MESSAGE);
    }
}
