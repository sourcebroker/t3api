<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use InvalidArgumentException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Service\UrlService;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

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

    public function __construct(
        public readonly LinkFactory $linkFactory,
        public readonly LinkService $linkService,
        public readonly ContentObjectRenderer $contentObjectRenderer
    ) {}

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

        try {
            $url = $this->linkFactory->createUri(
                sprintf('t3://record?identifier=%s&uid=%s', $type['params'][0], $entity->getUid()),
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

        return UrlService::forceAbsoluteUrl(
            $url,
            $context->getAttribute('TYPO3_SITE_URL')
        );
    }
}
