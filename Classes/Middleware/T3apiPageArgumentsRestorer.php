<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Routing\PageArguments;

/**
 * Restores page arguments replaced by T3apiPageArgumentsValidationBypass once TYPO3's
 * PageArgumentValidator has run.
 */
class T3apiPageArgumentsRestorer implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $originalPageArguments = $request->getAttribute(T3apiPageArgumentsValidationBypass::ORIGINAL_ROUTING_ATTRIBUTE);

        if ($originalPageArguments instanceof PageArguments) {
            $request = $request
                ->withAttribute('routing', $originalPageArguments)
                ->withoutAttribute(T3apiPageArgumentsValidationBypass::ORIGINAL_ROUTING_ATTRIBUTE);
        }

        return $handler->handle($request);
    }
}
