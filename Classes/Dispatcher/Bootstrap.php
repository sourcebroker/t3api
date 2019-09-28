<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Dispatcher;

use SourceBroker\T3api\Service\RouteService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use Exception;

/**
 * Class Bootstrap
 */
class Bootstrap extends AbstractDispatcher
{

    /** @var string */
    protected $output;

    /**
     * @return void
     * @throws Exception
     * @throws RouteNotFoundException
     */
    public function process(): void
    {
        $this->setLanguageAspect();
        $request = Request::createFromGlobals();
        $context = (new RequestContext())->fromRequest($request);
        $matchedRoute = null;

        if ($this->isMainEndpointResponseClassDefined() && $this->isContextMatchingMainEndpointRoute($context)) {
            $this->processMainEndpoint();
        } else {
            $this->output = $this->processOperationByRequest($context, $request);
        }

        $this->output();
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
        $routes->add('main_endpoint', new Route(rtrim(RouteService::getApiBasePath(), '/') . '/'));

        try {
            (new UrlMatcher($routes, $context))->match($context->getPathInfo());

            return true;
        } catch (ResourceNotFoundException $resourceNotFoundException) {
        }

        return false;
    }

    /**
     * @return void
     */
    protected function processMainEndpoint(): void
    {
        $this->output = $this->serializerService->serialize(
            $this->objectManager->get($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass'])
        );
    }

    /**
     * @return void
     * @todo add signal/hook just before the output?
     */
    protected function output(): void
    {
        echo $this->output;
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
