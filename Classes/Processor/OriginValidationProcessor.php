<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Processor;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Exception\OriginNotAllowedException;
use SourceBroker\T3api\Service\CorsService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Rejects requests changing data (all methods except GET, HEAD, OPTIONS and TRACE) sent from another origin which
 * is not allowed by the CORS configuration (`allowOrigin`), so the same origin policy applies to executing write
 * requests and to reading responses.
 *
 * Browsers send `Origin` with every such request; requests without it (e.g. server-to-server) are not affected.
 */
class OriginValidationProcessor implements ProcessorInterface
{
    public function __construct(private readonly CorsService $corsService) {}

    /**
     * @throws OriginNotAllowedException
     */
    public function process(Request $request, ResponseInterface &$response): void
    {
        if ($request->isMethodSafe() || !$this->corsService->isCorsRequest($request)) {
            return;
        }

        $origin = (string)$request->headers->get('Origin');
        if (!$this->corsService->isAllowedOrigin($origin, $this->corsService->getOptions())) {
            throw new OriginNotAllowedException($origin, $request->getMethod(), 1793646771);
        }
    }
}
