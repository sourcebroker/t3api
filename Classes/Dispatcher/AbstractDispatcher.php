<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Dispatcher;

use Exception;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Exception\RouteNotFoundException;
use SourceBroker\T3api\OperationHandler\OperationHandlerInterface;
use SourceBroker\T3api\Processor\ProcessorInterface;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Service\SerializerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException as SymfonyMethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException as SymfonyResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher as SignalSlotDispatcher;

/**
 * Class AbstractDispatcher
 */
abstract class AbstractDispatcher
{
    /**
     * @deprecated Move to another place in 2.0. Kept here only for backward compatibility.
     */
    public const SIGNAL_AFTER_DESERIALIZE_OPERATION = 'afterDeserializeOperation';

    /**
     * @deprecated Move to another place in 2.0. Kept here only for backward compatibility.
     */
    public const SIGNAL_AFTER_PROCESS_OPERATION = 'afterProcessOperation';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var SerializerService
     */
    protected $serializerService;

    /**
     * @var ApiResourceRepository
     */
    protected $apiResourceRepository;

    /**
     * Bootstrap constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->serializerService = $this->objectManager->get(SerializerService::class);
        $this->apiResourceRepository = $this->objectManager->get(ApiResourceRepository::class);
    }

    /**
     * @param RequestContext $requestContext
     * @param Request $request
     * @param ResponseInterface $response
     *
     * @throws Exception
     * @throws RouteNotFoundException
     * @return string
     */
    public function processOperationByRequest(
        RequestContext $requestContext,
        Request $request,
        ResponseInterface &$response = null
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
        ResponseInterface &$response = null
    ): string {
        $result = null;
        $handlers = $this->getHandlersSupportingOperation($operation, $request);

        if (empty($handlers)) {
            throw new RuntimeException(
                sprintf(
                    'Could not handle operation. Operation `%s` is unknown',
                    get_class($operation)
                ),
                1557506987081
            );
        }

        /** @var OperationHandlerInterface $handler */
        $handler = $this->objectManager->get(array_shift($handlers));
        $result = $handler->handle($operation, $request, $route ?? [], $response);

        $arguments = [
            'operation' => $operation,
            'result' => $result,
        ];
        $arguments = $this->objectManager->get(SignalSlotDispatcher::class)
            ->dispatch(__CLASS__, self::SIGNAL_AFTER_PROCESS_OPERATION, $arguments);

        return $result === null
            ? ''
            : $this->serializerService->serialize(
                $arguments['result'],
                SerializationContextBuilder::createFromOperation($operation, $request)
            );
    }

    protected function getHandlersSupportingOperation(OperationInterface $operation, Request $request): array
    {
        return array_filter(
            Configuration::getOperationHandlers(),
            static function (string $operationHandlerClass) use ($operation, $request) {
                if (!is_subclass_of($operationHandlerClass, OperationHandlerInterface::class, true)) {
                    throw new RuntimeException(
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

    protected function callProcessors(Request $request, &$response): void
    {
        $objectManager = $this->objectManager;
        array_filter(
            Configuration::getProcessors(),
            static function (string $processorClass) use ($request, &$response, $objectManager) {
                if (!is_subclass_of($processorClass, ProcessorInterface::class, true)) {
                    throw new RuntimeException(
                        sprintf(
                            'Process `%s` needs to be an instance of `%s`',
                            $processorClass,
                            ProcessorInterface::class
                        ),
                        1603705384
                    );
                }
                call_user_func_array([$objectManager->get($processorClass), 'process'], [$request, &$response]);
            }
        );
    }
}
