<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Response\AbstractCollectionResponse;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CollectionGetOperationHandler extends AbstractCollectionOperationHandler
{
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return parent::supports($operation, $request) && $operation->isMethodGet();
    }

    /** @noinspection ReferencingObjectsInspection */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        /** @var CollectionOperation $operation */
        parent::handle($operation, $request, $route, $response);
        $collectionResponseClass = Configuration::getCollectionResponseClass();
        $repository = $this->getRepositoryForOperation($operation);

        if (!is_subclass_of($collectionResponseClass, AbstractCollectionResponse::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Collection response class (`%s`) has to be an instance of `%s`',
                    $collectionResponseClass,
                    AbstractCollectionResponse::class
                )
            );
        }

        /** @var AbstractCollectionResponse $responseObject */
        $responseObject = GeneralUtility::makeInstance(
            $collectionResponseClass,
            $operation,
            $request,
            $repository->findFiltered($operation->getFilters(), $request)
        );

        return $responseObject;
    }
}
