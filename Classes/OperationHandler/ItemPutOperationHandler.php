<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\ResourceNotFoundException;
use SourceBroker\T3api\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class ItemPutOperationHandler extends AbstractItemOperationHandler
{
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return parent::supports($operation, $request) && $operation->isMethodPut();
    }

    /**
     * @return mixed|void
     * @noinspection ReferencingObjectsInspection
     * @throws OperationNotAllowedException
     * @throws ValidationException
     * @throws ResourceNotFoundException
     */
    public function handle(
        OperationInterface $operation,
        Request $request,
        array $route,
        ?ResponseInterface &$response
    ): AbstractDomainObject {
        /** @var ItemOperation $operation */
        $repository = $this->getRepositoryForOperation($operation);
        $object = parent::handle($operation, $request, $route, $response);

        $entityClass = $operation->getApiResource()->getEntity();
        /** @var AbstractDomainObject $newObject */
        $newObject = new $entityClass();

        foreach ($newObject->_getProperties() as $propertyName => $propertyValue) {
            if ($propertyName === 'uid') {
                continue;
            }
            $object->_setProperty($propertyName, $propertyValue);
        }

        $this->deserializeOperation($operation, $request, $object);
        $this->validationService->validateObject($object);
        $repository->add($object);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();

        return $object;
    }
}
