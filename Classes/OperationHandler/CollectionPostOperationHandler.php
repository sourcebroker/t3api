<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class CollectionPostOperationHandler extends AbstractCollectionOperationHandler
{
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return parent::supports($operation, $request) && $operation->isMethodPost();
    }

    /**
     * @return mixed|AbstractDomainObject|void
     * @throws ValidationException
     * @throws OperationNotAllowedException
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        /** @var CollectionOperation $operation */
        parent::handle($operation, $request, $route, $response);
        $repository = $this->getRepositoryForOperation($operation);

        $object = $this->deserializeOperation($operation, $request);
        $this->validationService->validateObject($object);
        $repository->add($object);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();

        $response = $response ? $response->withStatus(201) : $response;

        return $object;
    }
}
