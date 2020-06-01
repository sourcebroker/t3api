<?php
declare(strict_types=1);

namespace SourceBroker\T3api\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\Service\FileUploadService;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Extbase\Domain\Model\File;

class FileUploadOperationHandler extends AbstractCollectionOperationHandler
{
    /**
     * @var FileUploadService
     */
    protected $fileUploadService;

    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return parent::supports($operation, $request)
            && $operation->isMethodPost()
            && is_subclass_of($operation->getApiResource()->getEntity(), File::class, true);
    }

    public function injectFileUploadService(FileUploadService $fileUploadService): void
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param array $route
     * @param ResponseInterface|null $response
     * @throws OperationNotAllowedException
     * @throws Exception
     * @return mixed|\TYPO3\CMS\Core\Resource\File|void
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
