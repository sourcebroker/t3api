<?php
declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use SourceBroker\T3api\Dispatcher\AbstractDispatcher;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Repository\CommonRepository;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Security\OperationAccessChecker;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\ValidationService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher as SignalSlotDispatcher;

abstract class AbstractOperationHandler implements OperationHandlerInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var SerializerService
     */
    protected $serializerService;

    /**
     * @var ValidationService
     */
    protected $validationService;

    public function injectObjectManager(ObjectManagerInterface $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /** @noinspection PhpUnused */
    public function injectSerializerService(SerializerService $serializerService): void
    {
        $this->serializerService = $serializerService;
    }

    /** @noinspection PhpUnused */
    public function injectValidationService(ValidationService $validationService): void
    {
        $this->validationService = $validationService;
    }

    protected function getRepositoryForOperation(OperationInterface $operation): CommonRepository
    {
        return CommonRepository::getInstanceForOperation($operation);
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
            ->dispatch(AbstractDispatcher::class, AbstractDispatcher::SIGNAL_AFTER_DESERIALIZE_OPERATION, $arguments);

        return $arguments['object'];
    }
}
