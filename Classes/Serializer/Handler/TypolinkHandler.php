<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Service\UrlService;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class TypolinkHandler extends AbstractHandler implements SerializeHandlerInterface
{
    public const TYPE = 'Typolink';

    public function __construct(
        public readonly LinkFactory $linkFactory,
        public readonly LinkService $linkService,
        public readonly ContentObjectRenderer $contentObjectRenderer
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
        try {
            $url = $this->linkFactory->createUri(
                $typolinkParameter,
                $this->contentObjectRenderer
            )->getUrl();
        } catch (UnableToLinkException $e) {
            trigger_error(
                $e->getMessage(),
                E_USER_WARNING
            );
        }

        if (empty($url)) {
            return '';
        }

        return UrlService::forceAbsoluteUrl($url, $context->getAttribute('TYPO3_SITE_URL'));
    }
}
