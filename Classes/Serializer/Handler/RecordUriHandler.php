<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use InvalidArgumentException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Service\UrlService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
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
        /** @var AbstractDomainObject $entity */
        $entity = $context->getObject();

        if (!$entity instanceof AbstractDomainObject) {
            throw new InvalidArgumentException(
                sprintf('Object has to extend %s to build URI', AbstractDomainObject::class),
                1562229270419
            );
        }

        return UrlService::forceAbsoluteUrl(
            $this->getContentObjectRenderer()->getTypoLink_URL(sprintf(
                't3://record?identifier=%s&uid=%s',
                $type['params'][0],
                $entity->getUid()
            )),
            $context->getAttribute('TYPO3_SITE_URL')
        );
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
