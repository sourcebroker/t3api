<?php
declare(strict_types=1);

namespace SourceBroker\T3Api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
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
     * @param string $typolinkParameter
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
        return rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/')
            . $this->getContentObjectRenderer()->getTypoLink_URL($typolinkParameter);
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
