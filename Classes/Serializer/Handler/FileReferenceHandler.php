<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class FileReferenceHandler
 */
class FileReferenceHandler extends AbstractHandler implements SerializeHandlerInterface
{
    /**
     * @var string[]
     */
    protected static $supportedTypes = [FileReference::class];

    /**
     * @param SerializationVisitorInterface $visitor
     * @param FileReference $fileReference
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
        return [
            'uid' => $fileReference->getUid(),
            'url' => GeneralUtility::getIndpEnv('TYPO3_SITE_URL')
                . $fileReference->getOriginalResource()->getPublicUrl(),
        ];
    }
}
