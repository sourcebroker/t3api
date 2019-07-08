<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use InvalidArgumentException;

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
        /** @var AbstractEntity $entity */
        $entity = $context->getObject();

        if (!$entity instanceof AbstractEntity) {
            throw new InvalidArgumentException(
                sprintf('Object has to extend %s to build URI', AbstractEntity::class),
                1562229270419
            );
        }

        return rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/')
            . $this->getContentObjectRenderer()->getTypoLink_URL(sprintf(
                't3://record?identifier=%s&uid=%s',
                $type['params'][0],
                $entity->getUid()
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
