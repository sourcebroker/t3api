<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Core\Resource\FileReference as Typo3FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;

/**
 * Class FileReferenceHandler
 */
class FileReferenceHandler extends AbstractHandler implements SerializeHandlerInterface
{
    /**
     * @var string[]
     */
    protected static $supportedTypes = [
        ExtbaseFileReference::class,
        Typo3FileReference::class,
    ];

    /**
     * @param SerializationVisitorInterface $visitor
     * @param ExtbaseFileReference|Typo3FileReference $fileReference
     * @param array $type
     * @param SerializationContext $context
     *
     * @return array
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $fileReference,
        array $type,
        SerializationContext $context
    ) {
        $url = $fileReference instanceof ExtbaseFileReference
            ? $fileReference->getOriginalResource()->getPublicUrl()
            : $fileReference->getPublicUrl();

        return [
            'uid' => $fileReference->getUid(),
            'url' => GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $url,
        ];
    }
}
