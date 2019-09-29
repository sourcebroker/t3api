<?php
declare(strict_types=1);

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

/**
 * Class AbstractDispatcher
 */
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
     * @param RequestContext $requestContext
     * @param Request $request
     *
     * @return string
     * @throws Exception
     * @throws RouteNotFoundException
     */
    public function processOperationByRequest(RequestContext $requestContext, Request $request): string
    {
        foreach ($this->apiResourceRepository->getAll() as $apiResource) {
            try {
                $matchedRoute = (new UrlMatcher($apiResource->getRoutes(), $requestContext))
                    ->matchRequest($request);

                return $this->processOperation(
                    $apiResource->getOperationByRouteName($matchedRoute['_route']),
                    $matchedRoute,
                    $request
                );
            } catch (ResourceNotFoundException $resourceNotFoundException) {
                // @todo add comment
            }
        }

        throw new RouteNotFoundException('T3api resource not found for current route', 1557217186441);
    }

    /**
     * @param AbstractOperation $operation
     * @param array $matchedRoute
     * @param Request $request
     *
     * @return string
     * @throws Exception
     */
    protected function processOperation(AbstractOperation $operation, array $matchedRoute, Request $request): string
    {
        if ($operation instanceof ItemOperation) {
            $result = $this->processItemOperation($operation, (int)$matchedRoute['id'], $request);
        } elseif ($operation instanceof CollectionOperation) {
            $result = $this->processCollectionOperation($operation, $request);
        } else {
            // @todo throw appropriate exception
            throw new Exception('Unknown operation', 1557506987081);
        }

        return $this->serializerService->serializeOperation($operation, $result);
    }

    /**
     * @param ItemOperation $operation
     * @param int $uid
     * @param Request $request
     *
     * @return object
     *
     * @throws InvalidArgumentException
     */
    protected function processItemOperation(ItemOperation $operation, int $uid, Request $request)
    {
        $repository = CommonRepository::getInstanceForResource($operation->getApiResource());

        $object = $repository->findByUid($uid);

        if ($operation->getMethod() === 'PUT') {
            // @todo 591 implement support for PUT
        } elseif ($operation->getMethod() !== 'GET') {
            throw new InvalidArgumentException(
                sprintf('Method `%s` is not supported for item operation', $operation->getMethod()),
                1568378714606
            );
        } else {
            // @todo 591
            // throw new InvalidArgumentException();
        }

        if (!$object) {
            // @todo throw appropriate exception
            throw new InvalidArgumentException('Item not found');
        }

        return $object;
    }

    /**
     * @param CollectionOperation $operation
     * @param Request $request
     * @return object
     */
    protected function processCollectionOperation(CollectionOperation $operation, Request $request): object
    {
        $repository = CommonRepository::getInstanceForResource($operation->getApiResource());

        if ($operation->getMethod() === 'POST') {
            // @todo 591 implement support for POST
        } elseif ($operation->getMethod() === 'GET') {
            return $this->objectManager->get(
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'],
                $operation,
                $request,
                $repository->findFiltered($operation->getFilters(), $request)
            );
        } else {
            // @todo 591 throw appropriate exception
        }
    }
}
