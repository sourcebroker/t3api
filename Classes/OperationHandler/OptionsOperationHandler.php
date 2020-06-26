<?php

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Cors\Processor;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;

class OptionsOperationHandler extends AbstractOperationHandler
{
    /**
     * @var Processor
     */
    protected $corsProcessor;

    public function injectCorsProcessor(Processor $processor): void
    {
        $this->corsProcessor = $processor;
    }

    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return $request->getMethod() === 'OPTIONS';
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param array $route
     * @param ResponseInterface|null $response
     * @return mixed|void
     * @noinspection ReferencingObjectsInspection
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        $this->corsProcessor->processPreflight($request, $response);
    }
}
