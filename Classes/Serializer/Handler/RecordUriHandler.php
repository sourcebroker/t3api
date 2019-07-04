<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class RecordUriHandler
 */
class RecordUriHandler extends AbstractHandler implements SerializeHandlerInterface
{
    public const TYPE = 'RecordUri';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @param SerializationVisitorInterface $visitor
     * @param $value
     * @param array $type
     * @param SerializationContext $context
     *
     * @return string
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $value,
        array $type,
        SerializationContext $context
    ) {
        $uid = null;

        foreach ($context->getVisitingSet() as $item) {
            if ($item instanceof AbstractEntity) {
                $uid = $item->getUid();
            }
        };

        if (!$uid) {
            return '';
        }

        return rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/')
            . $this->getContentObjectRenderer()->getTypoLink_URL(sprintf(
                't3://record?identifier=%s&uid=%s',
                $type['params'][0],
                $uid
            ));
    }

    /**
     * @return ContentObjectRenderer
     */
    protected function getContentObjectRenderer(): ContentObjectRenderer
    {
        static $contentObjectRenderer;

        if (!$contentObjectRenderer instanceof ContentObjectRenderer) {
            $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        }

        return $contentObjectRenderer;
    }
}
