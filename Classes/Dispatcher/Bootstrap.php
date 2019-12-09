<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Dispatcher;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SourceBroker\T3api\Service\RouteService;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;

/**
 * Class Bootstrap
 */
class Bootstrap extends AbstractDispatcher
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var HttpFoundationFactory
     */
    protected $httpFoundationFactory;

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

    /**
     * @param ServerRequestInterface $request
     *
     * @throws Exception
     * @throws RouteNotFoundException
     * @return Response
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        $this->setLanguageAspect();
        $request = $this->httpFoundationFactory->createRequest($request);
        $context = (new RequestContext())->fromRequest($request);
        $matchedRoute = null;

        if ($this->isMainEndpointResponseClassDefined() && $this->isContextMatchingMainEndpointRoute($context)) {
            $output = $this->processMainEndpoint();
        } else {
            $output = $this->processOperationByRequest($context, $request, $this->response);
        }

        $this->response->getBody()->write($output);

        return $this->response;
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
     * Sets language aspect for context
     *
     * @return void
     */
    protected function setLanguageAspect(): void
    {
        $languageTsConfig = $GLOBALS['TSFE']->config;

        if (!isset($languageTsConfig['sys_language_uid'])) {
            $languageTsConfig['sys_language_uid'] = (int)($GLOBALS['TYPO3_REQUEST']->getQueryParams()['L'] ?? 0);
        }

        $this->objectManager->get(Context::class)
            ->setAspect('language', LanguageAspectFactory::createFromTypoScript($languageTsConfig));
    }
}
