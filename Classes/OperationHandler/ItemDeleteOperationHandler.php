<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class ItemDeleteOperationHandler extends AbstractItemOperationHandler
{
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return parent::supports($operation, $request) && $operation->isMethodDelete();
    }

    /**
     * @return mixed|null
     * @noinspection ReferencingObjectsInspection
     * @throws OperationNotAllowedException
     * @throws ResourceNotFoundException
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        /** @var ItemOperation $operation */
        $repository = $this->getRepositoryForOperation($operation);
        $object = parent::handle($operation, $request, $route, $response);
        $repository->remove($object);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();
        $object = null;

        return null;
    }
}
