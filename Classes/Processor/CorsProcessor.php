<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Processor;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Service\CorsService;
use Symfony\Component\HttpFoundation\Request;

class CorsProcessor implements ProcessorInterface
{
    public function __construct(private readonly ?CorsService $corsService) {}

    public function process(Request $request, ResponseInterface &$response): void
    {
        if ($this->corsService === null) {
            return;
        }

        $response = $this->corsService->addVary($response, ['Origin']);
        if (!$this->isCorsRequest($request) || $this->isPreflightRequest($request)) {
            return;
        }

        $response = $this->corsService->applyActualResponse($request, $response);
    }

    protected function isCorsRequest(Request $request): bool
    {
        return $this->corsService->isCorsRequest($request);
    }

    protected function isPreflightRequest(Request $request): bool
    {
        return $this->corsService->isPreflightRequest($request);
    }
}
