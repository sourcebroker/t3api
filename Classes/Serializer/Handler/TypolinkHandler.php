<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Attribute\AsSerializerHandler;
use SourceBroker\T3api\Service\UrlService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

#[AsSerializerHandler]
class TypolinkHandler extends AbstractHandler implements SerializeHandlerInterface
{
    /**
     * @var string
     */
    public const TYPE = 'Typolink';

    public function __construct(
        protected readonly ContentObjectRenderer $contentObjectRenderer
    ) {}

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    public function serialize(
        SerializationVisitorInterface $visitor,
        mixed $typolinkParameter,
        array $type,
        SerializationContext $context
    ): string {
        $url = $this->contentObjectRenderer->typoLink_URL([
            'parameter' => $typolinkParameter,
        ]);

        return UrlService::forceAbsoluteUrl($url, $context->getAttribute('TYPO3_SITE_URL'));
    }
}
