<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\EventDispatcher\EventDispatcherInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Domain\Repository\CommonRepository;
use SourceBroker\T3api\Event\AfterDeserializeOperationEvent;
use SourceBroker\T3api\Exception\BadRequestException;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Security\OperationAccessChecker;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Service\CommonDateFieldsService;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\SlugService;
use SourceBroker\T3api\Service\ValidationService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

abstract class AbstractOperationHandler implements OperationHandlerInterface
{
    protected SlugService $slugService;

    protected CommonDateFieldsService $commonDateFieldsService;

    public function __construct(
        protected readonly SerializerService $serializerService,
        protected readonly ValidationService $validationService,
        protected readonly OperationAccessChecker $operationAccessChecker,
        protected readonly DeserializationContextBuilder $deserializationContextBuilder,
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * Injected by method (not constructor) so that adding new dependency does not break handlers which extend this
     * class and override the constructor.
     */
    public function injectSlugService(SlugService $slugService): void
    {
        $this->slugService = $slugService;
    }

    /**
     * Injected by method (not constructor) so that adding new dependency does not break handlers which extend this
     * class and override the constructor.
     */
    public function injectCommonDateFieldsService(CommonDateFieldsService $commonDateFieldsService): void
    {
        $this->commonDateFieldsService = $commonDateFieldsService;
    }

    protected function getRepositoryForOperation(OperationInterface $operation): CommonRepository
    {
        return CommonRepository::getInstanceForOperation($operation);
    }

    /**
     * @throws BadRequestException
     * @throws OperationNotAllowedException
     */
    protected function deserializeOperation(
        OperationInterface $operation,
        Request $request,
        ?AbstractDomainObject $targetObject = null
    ): AbstractDomainObject {
        $content = $request->getContent();
        $this->assertJsonObject($content);

        $object = $this->serializerService->deserialize(
            $content,
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

    /**
     * Checked before deserialization, as the serializer reports malformed JSON with a generic runtime exception.
     *
     * @throws BadRequestException
     */
    protected function assertJsonObject(string $content): void
    {
        try {
            $data = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new BadRequestException('invalid_json', [$exception->getMessage()], 1793641753);
        }

        if (!$data instanceof \stdClass) {
            throw new BadRequestException('json_object', [], 1793641755);
        }
    }
}
