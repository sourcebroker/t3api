<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Dispatcher;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\ItemOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Domain\Repository\CommonRepository;
use SourceBroker\T3api\Exception\MethodNotAllowedException;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Exception\ResourceNotFoundException;
use SourceBroker\T3api\Exception\RouteNotFoundException;
use SourceBroker\T3api\Exception\ValidationException;
use SourceBroker\T3api\Response\AbstractCollectionResponse;
use SourceBroker\T3api\Security\OperationAccessChecker;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Service\FileUploadService;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\ValidationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException as SymfonyMethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException as SymfonyResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\File;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher as SignalSlotDispatcher;

/**
 * Class AbstractDispatcher
 */
abstract class AbstractDispatcher
{
    public const SIGNAL_AFTER_DESERIALIZE_OPERATION = 'afterDeserializeOperation';
    public const SIGNAL_AFTER_PROCESS_OPERATION = 'afterProcessOperation';

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
     * @var ValidationService
     */
    protected $validationService;

    /**
     * @var FileUploadService
     */
    protected $fileUploadService;

    /**
     * Bootstrap constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->serializerService = $this->objectManager->get(SerializerService::class);
        $this->apiResourceRepository = $this->objectManager->get(ApiResourceRepository::class);
        $this->validationService = $this->objectManager->get(ValidationService::class);
        $this->fileUploadService = $this->objectManager->get(FileUploadService::class);
    }

    /**
     * @param RequestContext $requestContext
     * @param Request $request
     * @param ResponseInterface $response
     *
     * @throws Exception
     * @throws RouteNotFoundException
     * @return string
     */
    public function processOperationByRequest(
        RequestContext $requestContext,
        Request $request,
        ResponseInterface &$response = null
    ): string {
        foreach ($this->apiResourceRepository->getAll() as $apiResource) {
            try {
                $matchedRoute = (new UrlMatcher($apiResource->getRoutes(), $requestContext))
                    ->matchRequest($request);

                return $this->processOperation(
                    $apiResource->getOperationByRouteName($matchedRoute['_route']),
                    $matchedRoute,
                    $request,
                    $response
                );
            } catch (SymfonyResourceNotFoundException $resourceNotFoundException) {
                // do not stop - continue to find correct route
            } catch (SymfonyMethodNotAllowedException $methodNotAllowedException) {
                // do not stop - continue to find correct route
            }
        }

        throw new RouteNotFoundException(1557217186441);
    }

    /**
     * @param OperationInterface $operation
     * @param array $matchedRoute
     * @param Request $request
     * @param ResponseInterface $response
     *
     * @throws Exception
     * @return string
     */
    protected function processOperation(
        OperationInterface $operation,
        array $matchedRoute,
        Request $request,
        ResponseInterface &$response = null
    ): string {
        if ($operation instanceof ItemOperation) {
            $result = $this->processItemOperation($operation, (int)$matchedRoute['id'], $request);
        } elseif ($operation instanceof CollectionOperation) {
            $result = $this->processCollectionOperation($operation, $request, $response);
        } else {
            throw new RuntimeException(
                sprintf(
                    'Could not process - Operation `%s` is unknown',
                    get_class($operation)
                ),
                1557506987081
            );
        }

        $arguments = [
            'operation' => $operation,
            'result' => $result,
        ];
        $arguments = $this->objectManager->get(SignalSlotDispatcher::class)
            ->dispatch(__CLASS__, self::SIGNAL_AFTER_PROCESS_OPERATION, $arguments);

        return $result === null
            ? ''
            : $this->serializerService->serialize(
                $arguments['result'],
                SerializationContextBuilder::createFromOperation($operation, $request)
            );
    }

    /**
     * @param ItemOperation $operation
     * @param int $uid
     * @param Request $request
     * @throws MethodNotAllowedException
     * @throws OperationNotAllowedException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws UnknownObjectException
     * @return AbstractDomainObject
     */
    protected function processItemOperation(
        ItemOperation $operation,
        int $uid,
        Request $request
    ): ?AbstractDomainObject {
        $repository = CommonRepository::getInstanceForOperation($operation);

        /** @var AbstractDomainObject|null $object */
        $object = $repository->findByUid($uid);

        if (!OperationAccessChecker::isGranted($operation, ['object' => $object])) {
            throw new OperationNotAllowedException($operation, 1574411504130);
        }

        if (!$object instanceof AbstractDomainObject) {
            throw new ResourceNotFoundException($operation->getApiResource()->getEntity(), $uid, 1581461016515);
        }

        if ($operation->isMethodPatch()) {
            $this->deserializeOperation($operation, $request, $object);
            $this->validationService->validateObject($object);
            $repository->update($object);
            $this->objectManager->get(PersistenceManager::class)->persistAll();
        } elseif ($operation->isMethodPut()) {
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
            $this->objectManager->get(PersistenceManager::class)->persistAll();
        } elseif ($operation->isMethodDelete()) {
            $repository->remove($object);
            $this->objectManager->get(PersistenceManager::class)->persistAll();
            $object = null;
        } elseif (!$operation->isMethodGet()) {
            throw new MethodNotAllowedException($operation, 1581494567091);
        }

        return $object;
    }

    /**
     * @param CollectionOperation $operation
     * @param Request $request
     * @param ResponseInterface $response
     * @throws MethodNotAllowedException
     * @throws OperationNotAllowedException
     * @throws ValidationException
     * @throws \TYPO3\CMS\Core\Resource\Exception
     * @return AbstractDomainObject|AbstractCollectionResponse
     */
    protected function processCollectionOperation(
        CollectionOperation $operation,
        Request $request,
        ResponseInterface &$response = null
    ) {
        $repository = CommonRepository::getInstanceForOperation($operation);

        if (!OperationAccessChecker::isGranted($operation)) {
            throw new OperationNotAllowedException($operation, 1574416639472);
        }

        if ($operation->isMethodGet()) {
            if (!is_subclass_of($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'], AbstractCollectionResponse::class)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Collection response class (`%s`) has to be an instance of `%s`',
                        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'],
                        AbstractCollectionResponse::class
                    )
                );
            }

            /** @var AbstractCollectionResponse $responseObject */
            $responseObject = $this->objectManager->get(
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'],
                $operation,
                $request,
                $repository->findFiltered($operation->getFilters(), $request)
            );

            return $responseObject;
        }

        if ($operation->isMethodPost()) {
            if (is_subclass_of($operation->getApiResource()->getEntity(), File::class, true)) {
                $object = $this->fileUploadService->process($operation, $request);
            } else {
                $object = $this->deserializeOperation($operation, $request);
                $this->validationService->validateObject($object);
                $repository->add($object);
                $this->objectManager->get(PersistenceManager::class)->persistAll();
            }

            $response = $response ? $response->withStatus(201) : $response;

            return $object;
        }

        throw new MethodNotAllowedException($operation, 1581460954134);
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param AbstractDomainObject|null $targetObject
     * @throws OperationNotAllowedException
     * @return AbstractDomainObject
     */
    protected function deserializeOperation(
        OperationInterface $operation,
        Request $request,
        ?AbstractDomainObject $targetObject = null
    ): AbstractDomainObject {
        $object = $this->serializerService->deserialize(
            $request->getContent(),
            $operation->getApiResource()->getEntity(),
            DeserializationContextBuilder::createFromOperation($operation, $request, $targetObject)
        );

        if (!OperationAccessChecker::isGrantedPostDenormalize($operation, ['object' => $object])) {
            throw new OperationNotAllowedException($operation, 1574782843388);
        }

        $arguments = [
            'operation' => $operation,
            'object' => $object,
        ];
        $arguments = $this->objectManager->get(SignalSlotDispatcher::class)
            ->dispatch(__CLASS__, self::SIGNAL_AFTER_DESERIALIZE_OPERATION, $arguments);

        return $arguments['object'];
    }
}
