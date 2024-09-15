<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Attribute\AsSerializerHandler;
use SourceBroker\T3api\Service\FileReferenceService;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

#[AsSerializerHandler]
class ImageHandler extends AbstractHandler implements SerializeHandlerInterface
{
    /**
     * @var string
     */
    public const TYPE = 'Image';

    public function __construct(private readonly ?FileReferenceService $fileReferenceService) {}

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @param FileReference|FileReference[]|int|int[] $fileReference
     *
     * @return string|string[]|null[]|null
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $fileReference,
        array $type,
        SerializationContext $context
    ): array|string|null {
        if (is_iterable($fileReference)) {
            return array_values(
                array_map(
                    function ($fileReference) use ($type, $context): ?string {
                        return $this->processSingleImage($fileReference, $type, $context);
                    },
                    $fileReference instanceof \Traversable ? iterator_to_array($fileReference) : $fileReference
                )
            );
        }

        return $this->processSingleImage($fileReference, $type, $context);
    }

    protected function processSingleImage(
        FileReference|int $fileReference,
        array $type,
        SerializationContext $context
    ): ?string {
        $processedFileUrl = null;
        try {
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
                'crop' => $this->getCropArea($fileResource, $type['params'][4] ?? 'default'),
            ]);
            $processedFileUrl = $this->fileReferenceService->getUrlFromResource($processedFile, $context);
        } catch (\Exception $e) {
            trigger_error(
                $e->getMessage(),
                E_USER_WARNING
            );
        }
        return $processedFileUrl;
    }

    protected function getCropArea($fileResource, string $cropVariant): ?Area
    {
        if ($fileResource->hasProperty('crop') && $fileResource->getProperty('crop')) {
            $cropString = $fileResource->getProperty('crop');
            $cropVariantCollection = CropVariantCollection::create((string)$cropString);
            $cropArea = $cropVariantCollection->getCropArea($cropVariant);
            return $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($fileResource);
        }

        return null;
    }
}
