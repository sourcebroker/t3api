<?php

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Service\CorsService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\Response;

class OptionsOperationHandler extends AbstractOperationHandler
{
    /**
     * @var CorsService
     */
    private $corsService;

    public function injectCorsService(CorsService $corsService): void
    {
        $this->corsService = $corsService;
    }

    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return $request->getMethod() === Request::METHOD_OPTIONS;
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param array $route
     * @param ResponseInterface|null $response
     *
     * @return mixed|void
     * @noinspection CallableParameterUseCaseInTypeContextInspection*/
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        $options = $this->corsService->getOptions();
        $response = $response instanceof ResponseInterface ? $response : new Response();

        if ($options->allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        if ($options->allowMethods) {
            $response = $response->withHeader('Access-Control-Allow-Methods', implode(', ', $options->allowMethods));
        }

        if ($options->allowHeaders) {
            $allowHeaders = $this->corsService->isWildcard($options->allowHeaders)
                ? $request->headers->get('Access-Control-Request-Headers')
                : implode(', ', $options->allowHeaders);

            if ($allowHeaders) {
                $response = $response->withHeader('Access-Control-Allow-Headers', $allowHeaders);
            }
        }

        if ($options->maxAge) {
            $response = $response->withHeader('Access-Control-Max-Age', (string)$options->maxAge);
        }

        if (!$this->corsService->isAllowedOrigin($request->headers->get('Origin'), $options)) {
            $response = $response->withoutHeader('Access-Control-Allow-Origin');

            return;
        }

        $response = $response->withHeader('Access-Control-Allow-Origin', $request->headers->get('Origin'));

        if (!in_array(strtoupper($request->headers->get('Access-Control-Request-Method')), $options->allowMethods, true)) {
            $response = $response->withStatus(405);

            return;
        }

        // Allow header in case-set received from client as some browsers may send it differently
        if (!in_array($request->headers->get('Access-Control-Request-Method'), $options->allowMethods, true)) {
            $allowMethods = array_merge($options->allowMethods, [$request->headers->get('Access-Control-Request-Method')]);
            $response = $response->withHeader('Access-Control-Allow-Methods', implode(', ', $allowMethods));
        }

        $headers = $request->headers->get('Access-Control-Request-Headers');
        if ($headers && !$this->corsService->isWildcard($options->allowHeaders)) {
            $headers = strtolower(trim($headers));
            foreach (preg_split('{, *}', $headers) as $header) {
                if (!in_array($header, $options->allowHeaders, true)) {
                    $response = $response->withStatus(405);

                    return 'Unauthorized header ' . $header;
                }
            }
        }
    }
}
