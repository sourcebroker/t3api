<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Configuration\CorsOptions;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CorsService
{
    public function getOptions(): CorsOptions
    {
        return GeneralUtility::makeInstance(CorsOptions::class, Configuration::getCors());
    }

    public function isCorsRequest(Request $request): bool
    {
        $origin = $request->headers->get('Origin');

        return $origin !== null && $origin !== '' && $origin !== $request->getSchemeAndHttpHost();
    }

    public function isPreflightRequest(Request $request): bool
    {
        return $request->isMethod(Request::METHOD_OPTIONS)
            && $request->headers->has('Origin')
            && $request->headers->has('Access-Control-Request-Method');
    }

    public function applyActualResponse(Request $request, ResponseInterface $response): ResponseInterface
    {
        // Include requests without Origin: a shared cache must not reuse those for CORS requests.
        $response = $this->addVary($this->removeCorsHeaders($response), ['Origin']);
        $options = $this->getOptions();
        $origin = (string)$request->headers->get('Origin');
        if ($origin === '' || !$this->isAllowedOrigin($origin, $options)) {
            return $response;
        }

        $response = $this->allowOrigin($response, $origin, $options);
        if ($options->exposeHeaders !== []) {
            $response = $response->withHeader('Access-Control-Expose-Headers', implode(', ', $options->exposeHeaders));
        }

        return $response;
    }

    public function applyPreflightResponse(Request $request, ResponseInterface $response): ResponseInterface
    {
        $response = $this->addVary($response, ['Origin', 'Access-Control-Request-Method', 'Access-Control-Request-Headers']);
        if (!$this->isPreflightRequest($request)) {
            return $response;
        }

        $response = $this->removeCorsHeaders($response);
        $options = $this->getOptions();
        $origin = (string)$request->headers->get('Origin');
        if ($origin === '' || !$this->isAllowedOrigin($origin, $options)) {
            return $response->withStatus(403);
        }

        $method = (string)$request->headers->get('Access-Control-Request-Method');
        if (!in_array(strtoupper($method), $options->allowMethods, true)) {
            return $response->withStatus(405);
        }

        $requestedHeaders = array_values(array_filter(array_map(
            static fn(string $header): string => strtolower(trim($header)),
            explode(',', (string)$request->headers->get('Access-Control-Request-Headers'))
        ), static fn(string $header): bool => $header !== ''));
        $wildcardHeaders = $this->isWildcard($options->allowHeaders);
        if (!$wildcardHeaders && array_diff($requestedHeaders, $options->allowHeaders) !== []) {
            return $response->withStatus(405);
        }

        // Grant access only after the complete preflight has been validated.
        $response = $this->allowOrigin($response, $origin, $options);
        // Preserve legacy case-insensitive approval while also advertising the exact requested spelling.
        $methods = array_unique([...$options->allowMethods, $method]);
        $response = $response->withHeader('Access-Control-Allow-Methods', implode(', ', $methods));

        // Echo concrete names for wildcard configuration, including credentialed requests.
        $headers = $wildcardHeaders ? $requestedHeaders : $options->allowHeaders;
        if ($headers !== []) {
            $response = $response->withHeader('Access-Control-Allow-Headers', implode(', ', array_unique($headers)));
        }
        if ($options->maxAge !== null) {
            $response = $response->withHeader('Access-Control-Max-Age', (string)$options->maxAge);
        }

        return $response;
    }

    public function isAllowedOrigin(string $origin, CorsOptions $options): bool
    {
        if ($this->isWildcard($options->allowOrigin)) {
            return true;
        }

        if ($options->originRegex) {
            foreach ($options->allowOrigin as $originRegexp) {
                if (preg_match('{\A(?:' . $originRegexp . ')\z}i', $origin) === 1) {
                    return true;
                }
            }
        } elseif (in_array($origin, $options->allowOrigin, true)) {
            return true;
        }

        return false;
    }

    public function isWildcard($option): bool
    {
        return $option === true
            || (is_array($option) && in_array('*', $option, true))
            || (is_string($option) && $option === '*');
    }

    private function allowOrigin(ResponseInterface $response, string $origin, CorsOptions $options): ResponseInterface
    {
        $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
        if ($options->allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    private function removeCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        foreach ([
            'Access-Control-Allow-Origin',
            'Access-Control-Allow-Credentials',
            'Access-Control-Allow-Methods',
            'Access-Control-Allow-Headers',
            'Access-Control-Expose-Headers',
            'Access-Control-Max-Age',
        ] as $header) {
            $response = $response->withoutHeader($header);
        }

        return $response;
    }

    public function addVary(ResponseInterface $response, array $headers): ResponseInterface
    {
        $vary = array_values(array_filter(array_map('trim', explode(',', $response->getHeaderLine('Vary')))));
        if (in_array('*', $vary, true)) {
            return $response;
        }
        foreach ($headers as $header) {
            if (!in_array(strtolower($header), array_map('strtolower', $vary), true)) {
                $vary[] = $header;
            }
        }

        return $response->withHeader('Vary', implode(', ', $vary));
    }
}
