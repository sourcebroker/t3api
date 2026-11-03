<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Exception\BadRequestException;
use SourceBroker\T3api\Exception\ExceptionInterface;
use SourceBroker\T3api\Exception\InternalServerErrorException;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Service\FileTokenServiceTrait;
use SourceBroker\T3api\Service\OperationResponseCache;
use SourceBroker\T3api\Service\RequestLanguageService;
use SourceBroker\T3api\Service\RouteService;
use SourceBroker\T3api\Service\SerializerService;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Bootstrap extends AbstractDispatcher implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use FileTokenServiceTrait;

    protected ResponseInterface $response;

    protected HttpFoundationFactory $httpFoundationFactory;

    public function __construct(
        SerializerService $serializerService,
        ApiResourceRepository $apiResourceRepository,
        SerializationContextBuilder $serializationContextBuilder,
        DeserializationContextBuilder $deserializationContextBuilder,
        EventDispatcherInterface $eventDispatcherInterface,
        OperationResponseCache $operationResponseCache
    ) {
        parent::__construct(
            $serializerService,
            $apiResourceRepository,
            $serializationContextBuilder,
            $deserializationContextBuilder,
            $eventDispatcherInterface,
            $operationResponseCache,
        );
        $this->response = new Response('php://temp', 200, ['Content-Type' => 'application/ld+json']);
        $this->httpFoundationFactory = GeneralUtility::makeInstance(HttpFoundationFactory::class);
    }

    /**
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $inputRequest): ResponseInterface
    {
        // Granted files and issued file tokens belong to one request (also when PHP process serves many requests)
        $this->getFileTokenService()->reset();
        try {
            return $this->processRequest($inputRequest);
        } finally {
            $this->getFileTokenService()->reset();
        }
    }

    /**
     * @throws \Throwable
     */
    protected function processRequest(ServerRequestInterface $inputRequest): ResponseInterface
    {
        try {
            $request = $this->httpFoundationFactory->createRequest($inputRequest);
            $context = (new RequestContext())->fromRequest($request);
            $this->callProcessors($request, $this->response);
            $languageHeaderException = $inputRequest->getAttribute(RequestLanguageService::ATTRIBUTE_LANGUAGE_HEADER_EXCEPTION);
            if ($languageHeaderException instanceof BadRequestException) {
                throw $languageHeaderException;
            }
            if ($this->isMainEndpointResponseClassDefined() && $this->isContextMatchingMainEndpointRoute($context)) {
                $output = $this->processMainEndpoint();
            } else {
                $output = $this->processOperationByRequest($context, $request, $this->response);
            }
        } catch (ExceptionInterface $exception) {
            $output = $this->serializerService->serialize($exception);
            $this->response = $this->response->withStatus($exception->getStatusCode(), $exception->getTitle());
        } catch (\Throwable $throwable) {
            $eventId = bin2hex(random_bytes(8));
            $this->logger?->error(
                't3api: unexpected exception, event ID {eventId}',
                ['eventId' => $eventId, 'exception' => $throwable]
            );
            try {
                // The message of an unexpected exception may contain paths, database details etc.
                $output = $this->serializerService->serialize(
                    SerializerService::isDebugMode() ? $throwable : new InternalServerErrorException($eventId)
                );
                $this->response = $this->response->withStatus(
                    SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR,
                    SymfonyResponse::$statusTexts[SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR]
                );
            } catch (\Throwable $throwableSerializationException) {
                throw $throwable;
            }
        }

        $this->response->getBody()->write($output);

        return $this->response;
    }

    protected function isMainEndpointResponseClassDefined(): bool
    {
        return !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass']);
    }

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

    protected function processMainEndpoint(): string
    {
        return $this->serializerService->serialize(
            GeneralUtility::makeInstance($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['mainEndpointResponseClass'])
        );
    }
}
