<?php

namespace SourceBroker\T3api\OperationHandler;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Security\OperationAccessChecker;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Service\CorsService;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\ValidationService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Http\Response;

class OptionsOperationHandler extends AbstractOperationHandler
{
    private ?CorsService $corsService;

    public function __construct(
        SerializerService $serializerService,
        ValidationService $validationService,
        OperationAccessChecker $operationAccessChecker,
        DeserializationContextBuilder $deserializationContextBuilder,
        EventDispatcherInterface $eventDispatcher,
        CorsService $corsService
    ) {
        parent::__construct(
            $serializerService,
            $validationService,
            $operationAccessChecker,
            $deserializationContextBuilder,
            $eventDispatcher
        );
        $this->corsService = $corsService;
    }

    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return $request->getMethod() === Request::METHOD_OPTIONS;
    }

    /**
     * @return mixed|void
     * @noinspection CallableParameterUseCaseInTypeContextInspection
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        $response = $this->corsService->applyPreflightResponse($request, $response ?? new Response());

        return null;
    }
}
