<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Processor;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Attribute\AsProcessor;
use SourceBroker\T3api\Service\CorsService;
use Symfony\Component\HttpFoundation\Request;

#[AsProcessor(priority: 100)]
class CorsProcessor implements ProcessorInterface
{
    public function __construct(private readonly ?CorsService $corsService) {}

    public function process(Request $request, ResponseInterface &$response): void
    {
        if (
            !$this->isCorsRequest($request)
            || $this->isPreflightRequest($request)
        ) {
            return;
        }

        $options = $this->corsService->getOptions();

        $requestOrigin = $request->headers->get('Origin');

        if (!$this->corsService->isAllowedOrigin($requestOrigin, $options)) {
            $response = $response->withoutHeader('Access-Control-Allow-Origin');
        }

        $response = $response->withHeader(
            'Access-Control-Allow-Origin',
            $requestOrigin
        );

        if ($options->allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        if ($options->exposeHeaders !== []) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                strtolower(implode(', ', $options->exposeHeaders))
            );
        }
    }

    protected function isCorsRequest(Request $request): bool
    {
        return $request->headers->has('Origin')
            && $request->headers->get('Origin')
            !== $request->getSchemeAndHttpHost();
    }

    protected function isPreflightRequest(Request $request): bool
    {
        return $request->getMethod() === Request::METHOD_OPTIONS;
    }
}
