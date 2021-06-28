<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Service\UrlService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class TypolinkHandler
 */
class TypolinkHandler extends AbstractHandler implements SerializeHandlerInterface
{
    public const TYPE = 'Typolink';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @param SerializationVisitorInterface $visitor
     * @param string|int|array $typolinkParameter
     * @param array $type
     * @param SerializationContext $context
     *
     * @return array|string
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $typolinkParameter,
        array $type,
        SerializationContext $context
    ) {
        return UrlService::forceAbsoluteUrl(
            $this->getContentObjectRenderer()->getTypoLink_URL(...(array)$typolinkParameter),
            $context->getAttribute('TYPO3_SITE_URL'),
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
