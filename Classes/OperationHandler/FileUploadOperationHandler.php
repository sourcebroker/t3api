<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Security\OperationAccessChecker;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Service\FileUploadService;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\ValidationService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Extbase\Domain\Model\File;

class FileUploadOperationHandler extends AbstractCollectionOperationHandler
{
    protected FileUploadService $fileUploadService;

    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return parent::supports($operation, $request)
            && $operation->isMethodPost()
            && is_subclass_of($operation->getApiResource()->getEntity(), File::class, true);
    }

    public function __construct(
        FileUploadService $fileUploadService,
        SerializerService $serializerService,
        ValidationService $validationService,
        OperationAccessChecker $operationAccessChecker,
        DeserializationContextBuilder $deserializationContextBuilder,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct(
            $serializerService,
            $validationService,
            $operationAccessChecker,
            $deserializationContextBuilder,
            $eventDispatcher
        );
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * @return mixed|\TYPO3\CMS\Core\Resource\File|void
     * @throws Exception
     * @throws OperationNotAllowedException
     */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        /** @var CollectionOperation $operation */
        parent::handle($operation, $request, $route, $response);

        $object = $this->fileUploadService->process($operation, $request);

        $response = $response ? $response->withStatus(201) : $response;

        return $object;
    }
}
