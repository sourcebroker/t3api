<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Dispatcher;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use SourceBroker\T3api\Exception\ExceptionInterface;
use SourceBroker\T3api\Service\RouteService;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Throwable;
use TYPO3\CMS\Core\Http\Response;

/**
 * @deprecated Will be removed when support for TYPO3 <= 9.4 is dropped
 */
class LegacyTypoScriptDispatcher extends AbstractDispatcher
{
    /**
     * @var Request
     */
    protected static $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var HttpFoundationFactory
     */
    protected $httpFoundationFactory;

    public static function storeRequest(): void
    {
        self::$request = Request::createFromGlobals();
    }

    /**
     * Bootstrap constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->response = new Response('php://temp', 200, ['Content-Type' => 'application/ld+json']);
    }

    /**
     * @param HttpFoundationFactory $httpFoundationFactory
     */
    public function injectHttpFoundationFactory(HttpFoundationFactory $httpFoundationFactory)
    {
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    public function process(): ResponseInterface
    {
        try {
            $request = self::$request;
            $context = (new RequestContext())->fromRequest($request);
            $matchedRoute = null;

            if ($this->isMainEndpointResponseClassDefined() && $this->isContextMatchingMainEndpointRoute($context)) {
                $output = $this->processMainEndpoint();
            } else {
                $output = $this->processOperationByRequest($context, $request, $this->response);
            }
        } catch (ExceptionInterface $exception) {
            $output = $this->serializerService->serialize($exception);
            $this->response = $this->response->withStatus($exception->getStatusCode(), $exception->getTitle());
        } catch (Throwable $throwable) {
            $output = $this->serializerService->serialize($throwable);
            $this->response = $this->response->withStatus(
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR,
                SymfonyResponse::$statusTexts[SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR]
            );
        }

        $this->response->getBody()->write($output);

        $this->sendResponseAndDie();
    }

    /**
     * @return bool
     */
    protected function isMainEndpointResponseClassDefined(): bool
    {
        return !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass']);
    }

    /**
     * @param RequestContext $context
     *
     * @return bool
     */
    protected function isContextMatchingMainEndpointRoute(RequestContext $context): bool
    {
        $routes = (new RouteCollection());
        $routes->add('main_endpoint', new Route(RouteService::getFullApiBasePath() . '/'));
        $routes->add('main_endpoint_bis', new Route(RouteService::getFullApiBasePath()));

        try {
            (new UrlMatcher($routes, $context))->match($context->getPathInfo());

            return true;
        } catch (ResourceNotFoundException $resourceNotFoundException) {
        }

        return false;
    }

    /**
     * @return string
     */
    protected function processMainEndpoint(): string
    {
        return $this->serializerService->serialize(
            $this->objectManager->get($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass'])
        );
    }

    /**
     * @see \TYPO3\CMS\Core\Http\AbstractApplication::sendResponse (in TYPO3 >= 9.2)
     */
    protected function sendResponseAndDie(): void
    {
        $response = $this->response;

        if (!headers_sent()) {
            // If the response code was not changed by legacy code (still is 200)
            // then allow the PSR-7 response object to explicitly set it.
            // Otherwise let legacy code take precedence.
            // This code path can be deprecated once we expose the response object to third party code
            if (http_response_code() === 200) {
                header('HTTP/' . $response->getProtocolVersion() . ' ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
            }

            foreach ($response->getHeaders() as $name => $values) {
                if (strtolower($name) === ['set-cookie']) {
                    foreach ($values as $value) {
                        header($name . ': ' . $value, false);
                    }
                } else {
                    header($name . ': ' . implode(', ', $values));
                }
            }
        }
        echo $response->getBody()->__toString();
        die();
    }
}
