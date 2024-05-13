<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class RteHandler extends AbstractHandler implements SerializeHandlerInterface
{
    /**
     * @var string
     */
    public const TYPE = 'Rte';

    protected static $supportedTypes = [self::TYPE];

    // @todo make configuration reference `lib.parseFunc_RTE` configurable
    public function serialize(
        SerializationVisitorInterface $visitor,
        $text,
        array $type,
        SerializationContext $context
    ): string {
        return $this->getContentObjectRenderer()
            ->parseFunc($text, [], '< lib.parseFunc_RTE');
    }

    protected function getContentObjectRenderer(): ContentObjectRenderer
    {
        static $contentObjectRenderer;

        if (!$contentObjectRenderer instanceof ContentObjectRenderer) {
            $contentObjectRenderer
                = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        }

        return $contentObjectRenderer;
    }
}
