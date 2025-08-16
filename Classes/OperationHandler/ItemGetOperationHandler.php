<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Attribute\AsOperationHandler;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

#[AsOperationHandler(priority: -500)]
class ItemGetOperationHandler extends AbstractItemOperationHandler
{
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return parent::supports($operation, $request) && $operation->isMethodGet();
    }

    /**
     * @noinspection ReferencingObjectsInspection
     * @throws OperationNotAllowedException
     * @throws ResourceNotFoundException
     */
    public function handle(
        OperationInterface $operation,
        Request $request,
        array $route,
        ?ResponseInterface &$response
    ): AbstractDomainObject {
        /** @var ItemOperation $operation */
        return parent::handle($operation, $request, $route, $response);
    }
}
