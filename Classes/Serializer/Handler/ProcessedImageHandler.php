<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ProcessedImageHandler
 */
class ProcessedImageHandler extends AbstractHandler implements SerializeHandlerInterface
{
    public const TYPE = 'ProcessedImage';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @param SerializationVisitorInterface $visitor
     * @param FileReference|int $fileReference
     * @param array $type
     * @param SerializationContext $context
     *
     * @return array|string
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $fileReference,
        array $type,
        SerializationContext $context
    ) {
        $fileResource = null;

        if (is_int($fileReference)) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $fileRepository = $objectManager->get(FileRepository::class);
            $fileResource = $fileRepository->findFileReferenceByUid($fileReference);
        } else {
            $fileResource = $fileReference->getOriginalResource();
        }

        $file = $fileResource->getOriginalFile();
        $file = $file->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
            'width' => $type['params'][0] ?? '',
            'height' => $type['params'][1] ?? '',
        ]);

        return GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $file->getPublicUrl();
    }
}
