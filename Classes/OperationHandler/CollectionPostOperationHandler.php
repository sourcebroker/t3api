<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class CollectionPostOperationHandler extends AbstractCollectionOperationHandler
{
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return parent::supports($operation, $request) && $operation->isMethodPost();
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param array $route
     * @param ResponseInterface|null $response
     * @throws OperationNotAllowedException
     * @throws ValidationException
     * @return mixed|AbstractDomainObject|void
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        /** @var CollectionOperation $operation */
        parent::handle($operation, $request, $route, $response);
        $repository = $this->getRepositoryForOperation($operation);

        $object = $this->deserializeOperation($operation, $request);
        $this->validationService->validateObject($object);
        $repository->add($object);
        $this->objectManager->get(PersistenceManager::class)->persistAll();

        $response = $response ? $response->withStatus(201) : $response;

        return $object;
    }
}
