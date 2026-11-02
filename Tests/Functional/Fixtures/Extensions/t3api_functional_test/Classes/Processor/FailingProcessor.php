<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Processor;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Throws an unexpected exception whose message contains internal details, like a failing
 * database connection would.
 */
class FailingProcessor implements ProcessorInterface
{
    public const MESSAGE = 'Database connection failed for user db_user at /var/www/config/system/settings.php';

    public function process(Request $request, ResponseInterface &$response): void
    {
        throw new \RuntimeException(self::MESSAGE, 1793285052);
    }
}
