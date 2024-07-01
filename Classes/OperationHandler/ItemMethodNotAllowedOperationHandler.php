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
     * @noinspection ReferencingObjectsInspection
     * @throws MethodNotAllowedException
     */
    public function handle(
        OperationInterface $operation,
        Request $request,
        array $route,
        ?ResponseInterface &$response
    ): never {
        throw new MethodNotAllowedException($operation, 1581494567091);
    }
}
