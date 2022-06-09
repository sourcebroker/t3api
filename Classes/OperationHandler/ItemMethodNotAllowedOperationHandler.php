<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\MethodNotAllowedException;
use Symfony\Component\HttpFoundation\Request;

class ItemMethodNotAllowedOperationHandler extends AbstractItemOperationHandler
{
    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param array $route
     * @param ResponseInterface|null $response
     * @throws MethodNotAllowedException
     * @return mixed|void
     * @noinspection ReferencingObjectsInspection
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        throw new MethodNotAllowedException($operation, 1581494567091);
    }
}
