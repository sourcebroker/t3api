<?php

namespace SourceBroker\T3api\Dispatcher;

use Exception;
use InvalidArgumentException;
use SourceBroker\T3api\Domain\Model\AbstractOperation;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Domain\Repository\CommonRepository;
use SourceBroker\T3api\Service\SerializerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class AbstractDispatcher
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var SerializerService
     */
    protected $serializerService;

    /**
     * @var ApiResourceRepository
     */
    protected $apiResourceRepository;

    /**
     * Bootstrap constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->serializerService = $this->objectManager->get(SerializerService::class);
        $this->apiResourceRepository = $this->objectManager->get(ApiResourceRepository::class);
    }

    /**
     * @return string
     * @throws Exception
     * @throws RouteNotFoundException
     */
    protected function processRequestByContext(RequestContext $requestContext): string
    {
        foreach ($this->apiResourceRepository->getAll() as $apiResource) {
            try {
                $matchedRoute = (new UrlMatcher($apiResource->getRoutes(), $requestContext))->match($requestContext->getPathInfo());

                return $this->processOperation(
                    $apiResource->getOperationByRouteName($matchedRoute['_route']),
                    $matchedRoute
                );
            } catch (ResourceNotFoundException $resourceNotFoundException) {
            }
        }

        throw new RouteNotFoundException('T3api resource not found for current route', 1557217186441);
    }

    /**
     * @param AbstractOperation $operation
     * @param array $matchedRoute
     *
     * @return string
     *
     * @throws Exception
     */
    protected function processOperation(AbstractOperation $operation, array $matchedRoute): string
    {
        $repository = CommonRepository::getInstanceForEntity($operation->getApiResource()->getEntity());

        if ($operation instanceof ItemOperation) {
            $result = $repository->findByUid((int)$matchedRoute['id']);

            if (!$result) {
                // @todo throw appropriate exception
                throw new InvalidArgumentException('Item not found');
            }
        } elseif ($operation instanceof CollectionOperation) {
            $result = $this->objectManager->get(
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'],
                $operation,
                $repository->findFiltered($operation->getFilters())
            );
        } else {
            // @todo throw appropriate exception
            throw new Exception('Unknown operation', 1557506987081);
        }

        return $this->serializerService->serializeOperation($operation, $result);
    }

}
