<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Repository\CommonRepository;
use SourceBroker\T3api\Event\AfterDeserializeOperationEvent;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Security\OperationAccessChecker;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\ValidationService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

abstract class AbstractOperationHandler implements OperationHandlerInterface
{
    /**
     * @var SerializerService
     */
    protected $serializerService;

    /**
     * @var ValidationService
     */
    protected $validationService;

    /**
     * @var OperationAccessChecker
     */
    protected $operationAccessChecker;

    /**
     * @var DeserializationContextBuilder
     */
    protected $deserializationContextBuilder;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        SerializerService $serializerService,
        ValidationService $validationService,
        OperationAccessChecker $operationAccessChecker,
        DeserializationContextBuilder $deserializationContextBuilder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->serializerService = $serializerService;
        $this->validationService = $validationService;
        $this->operationAccessChecker = $operationAccessChecker;
        $this->deserializationContextBuilder = $deserializationContextBuilder;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getRepositoryForOperation(OperationInterface $operation): CommonRepository
    {
        return CommonRepository::getInstanceForOperation($operation);
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param AbstractDomainObject|null $targetObject
     * @return AbstractDomainObject
     * @throws OperationNotAllowedException
     */
    protected function deserializeOperation(
        OperationInterface $operation,
        Request $request,
        ?AbstractDomainObject $targetObject = null
    ): AbstractDomainObject {
        $object = $this->serializerService->deserialize(
            $request->getContent(),
            $operation->getApiResource()->getEntity(),
            $this->deserializationContextBuilder->createFromOperation($operation, $request, $targetObject)
        );

        if (!$this->operationAccessChecker->isGrantedPostDenormalize($operation, ['object' => $object])) {
            throw new OperationNotAllowedException($operation, 1574782843388);
        }

        $afterDeserializeOperationEvent = new AfterDeserializeOperationEvent(
            $operation,
            $object
        );
        $this->eventDispatcher->dispatch($afterDeserializeOperationEvent);

        return $afterDeserializeOperationEvent->getObject();
    }
}
