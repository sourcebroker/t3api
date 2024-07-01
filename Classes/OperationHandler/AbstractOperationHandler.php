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
    public function __construct(
        protected readonly SerializerService $serializerService,
        protected readonly ValidationService $validationService,
        protected readonly OperationAccessChecker $operationAccessChecker,
        protected readonly DeserializationContextBuilder $deserializationContextBuilder,
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {}

    protected function getRepositoryForOperation(OperationInterface $operation): CommonRepository
    {
        return CommonRepository::getInstanceForOperation($operation);
    }

    /**
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
