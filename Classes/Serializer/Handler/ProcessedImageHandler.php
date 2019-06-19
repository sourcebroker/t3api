<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class ProcessedImageHandler
 *
 * @package SourceBroker\Restify\Serializer\Handler
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
     * @param FileReference $fileReference
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
        $file = $fileReference->getOriginalResource()->getOriginalFile();
        $file = $file->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
            'width' => $type['params'][0] ?? '',
            'height' => $type['params'][1] ?? '',
        ]);

        return GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $file->getPublicUrl();
    }
}
