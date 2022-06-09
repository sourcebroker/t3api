<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

abstract class AbstractItemOperationHandler extends AbstractOperationHandler
{
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return $operation instanceof ItemOperation;
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param array $route
     * @param ResponseInterface|null $response
     * @throws OperationNotAllowedException
     * @throws ResourceNotFoundException
     * @return mixed|AbstractDomainObject|null
     * @noinspection ReferencingObjectsInspection
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        $repository = $this->getRepositoryForOperation($operation);

        /** @var AbstractDomainObject|null $object */
        $object = $repository->findByUid((int)$route['id']);

        if (!$object instanceof AbstractDomainObject) {
            throw new ResourceNotFoundException($operation->getApiResource()->getEntity(), (int)$route['id'], 1581461016515);
        }

        if (!$this->operationAccessChecker->isGranted($operation, ['object' => $object])) {
            throw new OperationNotAllowedException($operation, 1574411504130);
        }

        return $object;
    }
}
