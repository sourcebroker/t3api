<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Event\AfterProcessOperationEvent;
use SourceBroker\T3api\Exception\RouteNotFoundException;
use SourceBroker\T3api\OperationHandler\OperationHandlerInterface;
use SourceBroker\T3api\Processor\ProcessorInterface;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Service\SerializerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException as SymfonyMethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException as SymfonyResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractDispatcher
{
    protected SerializerService $serializerService;

    protected ApiResourceRepository $apiResourceRepository;

    protected EventDispatcherInterface $eventDispatcher;

    protected SerializationContextBuilder $serializationContextBuilder;

    protected DeserializationContextBuilder $deserializationContextBuilder;

    public function __construct(
        SerializerService $serializerService,
        ApiResourceRepository $apiResourceRepository,
        SerializationContextBuilder $serializationContextBuilder,
        DeserializationContextBuilder $deserializationContextBuilder,
        EventDispatcherInterface $eventDispatcherInterface
    ) {
        $this->serializerService = $serializerService;
        $this->apiResourceRepository = $apiResourceRepository;
        $this->serializationContextBuilder = $serializationContextBuilder;
        $this->deserializationContextBuilder = $deserializationContextBuilder;
        $this->eventDispatcher = $eventDispatcherInterface;
    }

    /**
     * @throws RouteNotFoundException
     * @throws \Exception
     */
    public function processOperationByRequest(
        RequestContext $requestContext,
        Request $request,
        ?ResponseInterface &$response = null
    ): string {
        foreach ($this->apiResourceRepository->getAll() as $apiResource) {
            try {
                $matchedRoute = (new UrlMatcher($apiResource->getRoutes(), $requestContext))
                    ->matchRequest($request);

                return $this->processOperation(
                    $apiResource->getOperationByRouteName($matchedRoute['_route']),
                    $matchedRoute,
                    $request,
                    $response
                );
            } catch (SymfonyResourceNotFoundException $resourceNotFoundException) {
                // do not stop - continue to find correct route
            } catch (SymfonyMethodNotAllowedException $methodNotAllowedException) {
                // do not stop - continue to find correct route
            }
        }

        throw new RouteNotFoundException(1557217186441);
    }

    /**
     * @param OperationInterface $operation
     * @param array $route
     * @param Request $request
     * @param ResponseInterface|null $response
     * @return string
     */
    protected function processOperation(
        OperationInterface $operation,
        array $route,
        Request $request,
        ?ResponseInterface &$response = null
    ): string {
        $handlers = $this->getHandlersSupportingOperation($operation, $request);

        if ($handlers === []) {
            throw new \RuntimeException(
                sprintf(
                    'Could not handle operation. Operation `%s` is unknown',
                    get_class($operation)
                ),
                1557506987081
            );
        }

        /** @var OperationHandlerInterface $handler */
        $handler = GeneralUtility::makeInstance(array_shift($handlers));
        $result = $handler->handle($operation, $request, $route, $response);

        $afterProcessOperationEvent = new AfterProcessOperationEvent(
            $operation,
            $result
        );
        $this->eventDispatcher->dispatch($afterProcessOperationEvent);
        $result = $afterProcessOperationEvent->getResult();

        return $result === null
            ? ''
            : $this->serializerService->serialize(
                $result,
                $this->serializationContextBuilder->createFromOperation($operation, $request)
            );
    }

    protected function getHandlersSupportingOperation(OperationInterface $operation, Request $request): array
    {
        return array_filter(
            Configuration::getOperationHandlers(),
            static function (string $operationHandlerClass) use ($operation, $request) {
                if (!is_subclass_of($operationHandlerClass, OperationHandlerInterface::class, true)) {
                    throw new \RuntimeException(
                        sprintf(
                            'Operation handler `%s` needs to be an instance of `%s`',
                            $operationHandlerClass,
                            OperationHandlerInterface::class
                        ),
                        1591018489732
                    );
                }

                return call_user_func($operationHandlerClass . '::supports', $operation, $request);
            }
        );
    }

    protected function callProcessors(Request $request, ResponseInterface &$response): void
    {
        array_filter(
            Configuration::getProcessors(),
            static function (string $processorClass) use ($request, &$response) {
                if (!is_subclass_of($processorClass, ProcessorInterface::class, true)) {
                    throw new \RuntimeException(
                        sprintf(
                            'Process `%s` needs to be an instance of `%s`',
                            $processorClass,
                            ProcessorInterface::class
                        ),
                        1603705384
                    );
                }

                /** @var ProcessorInterface $processor */
                $processor = GeneralUtility::makeInstance($processorClass);
                $processor->process($request, $response);
            }
        );
    }
}
