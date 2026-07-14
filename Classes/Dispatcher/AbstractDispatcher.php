<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Event\AfterProcessOperationEvent;
use SourceBroker\T3api\Exception\RouteNotFoundException;
use SourceBroker\T3api\Factory\OperationHandlerCollectionFactory;
use SourceBroker\T3api\Factory\ProcessorsCollectionFactory;
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

abstract class AbstractDispatcher
{
    protected SerializerService $serializerService;

    protected ApiResourceRepository $apiResourceRepository;

    protected EventDispatcherInterface $eventDispatcher;

    protected SerializationContextBuilder $serializationContextBuilder;

    protected DeserializationContextBuilder $deserializationContextBuilder;

    protected OperationHandlerCollectionFactory $operationHandlerCollectionFactory;

    protected ProcessorsCollectionFactory $processorsCollectionFactory;

    public function __construct(
        SerializerService $serializerService,
        ApiResourceRepository $apiResourceRepository,
        SerializationContextBuilder $serializationContextBuilder,
        DeserializationContextBuilder $deserializationContextBuilder,
        EventDispatcherInterface $eventDispatcherInterface,
        OperationHandlerCollectionFactory $operationHandlerCollectionFactory,
        ProcessorsCollectionFactory $processorsCollectionFactory
    ) {
        $this->serializerService = $serializerService;
        $this->apiResourceRepository = $apiResourceRepository;
        $this->serializationContextBuilder = $serializationContextBuilder;
        $this->deserializationContextBuilder = $deserializationContextBuilder;
        $this->eventDispatcher = $eventDispatcherInterface;
        $this->operationHandlerCollectionFactory = $operationHandlerCollectionFactory;
        $this->processorsCollectionFactory = $processorsCollectionFactory;

        $this->init();
    }

    abstract protected function init(): void;

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

        $handler = array_shift($handlers);
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
            $this->operationHandlerCollectionFactory->get(),
            static function ($operationHandler) use ($operation, $request) {
                if (!$operationHandler instanceof OperationHandlerInterface) {
                    throw new \RuntimeException(
                        sprintf(
                            'Operation handler `%s` needs to be an instance of `%s`',
                            $operationHandler::class,
                            OperationHandlerInterface::class
                        ),
                        1591018489732
                    );
                }
                return $operationHandler::supports($operation, $request);
            }
        );
    }

    protected function callProcessors(Request $request, ResponseInterface &$response): void
    {
        array_filter(
            $this->processorsCollectionFactory->get(),
            static function ($processor) use ($request, &$response) {
                if (!$processor instanceof ProcessorInterface) {
                    throw new \RuntimeException(
                        sprintf(
                            'Process `%s` needs to be an instance of `%s`',
                            $processor::class,
                            ProcessorInterface::class
                        ),
                        1603705384
                    );
                }

                $processor->process($request, $response);
            }
        );
    }
}
