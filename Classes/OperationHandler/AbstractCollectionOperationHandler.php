<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCollectionOperationHandler extends AbstractOperationHandler
{
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return $operation instanceof CollectionOperation;
    }

    /**
     * @return mixed|void
     * @noinspection ReferencingObjectsInspection
     * @throws OperationNotAllowedException
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        if (!$this->operationAccessChecker->isGranted($operation)) {
            throw new OperationNotAllowedException($operation, 1574416639472);
        }
    }
}
