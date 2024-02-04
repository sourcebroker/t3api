<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Service\FileReferenceService;
use Traversable;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class ImageHandler
 */
class ImageHandler extends AbstractHandler implements SerializeHandlerInterface
{
    public const TYPE = 'Image';

    /**
     * @var FileReferenceService
     */
    private $fileReferenceService;

    public function __construct(FileReferenceService $fileReferenceService)
    {
        $this->fileReferenceService = $fileReferenceService;
    }

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @param SerializationVisitorInterface $visitor
     * @param FileReference|FileReference[]|int|int[] $fileReference
     * @param array $type
     * @param SerializationContext $context
     *
     * @return string|string[]|null[]|null
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $fileReference,
        array $type,
        SerializationContext $context
    ) {
        if (is_iterable($fileReference)) {
            return array_values(
                array_map(
                    function ($fileReference) use ($type, $context) {
                        return $this->processSingleImage($fileReference, $type, $context);
                    },
                    $fileReference instanceof Traversable ? iterator_to_array($fileReference) : $fileReference
                )
            );
        }

        return $this->processSingleImage($fileReference, $type, $context);
    }

    /**
     * @param FileReference|int $fileReference
     * @param array $type
     * @param SerializationContext $context
     * @return string|null
     */
    protected function processSingleImage($fileReference, array $type, SerializationContext $context): ?string
    {
        if (is_int($fileReference)) {
            $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
            $fileResource = $fileRepository->findFileReferenceByUid($fileReference);
        } else {
            $fileResource = $fileReference->getOriginalResource();
        }

        $file = $fileResource->getOriginalFile();
        $processedFile = $file->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
            'width' => $type['params'][0] ?? '',
            'height' => $type['params'][1] ?? '',
            'maxWidth' => $type['params'][2] ?? '',
            'maxHeight' => $type['params'][3] ?? '',
        ]);

        return $this->fileReferenceService->getUrlFromResource($processedFile, $context);
    }
}
