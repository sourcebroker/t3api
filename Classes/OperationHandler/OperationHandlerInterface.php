<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;

interface OperationHandlerInterface
{
    public static function supports(OperationInterface $operation, Request $request): bool;

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param array $route
     * @param ResponseInterface $response
     * @return mixed
     * @noinspection ReferencingObjectsInspection
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response);
}
