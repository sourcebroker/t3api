<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\T3api\Service\UrlService;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class RecordUriHandler extends AbstractHandler implements SerializeHandlerInterface
{
    /**
     * @var string
     */
    public const TYPE = 'RecordUri';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    public function __construct(
        protected readonly ContentObjectRenderer $contentObjectRenderer
    ) {}

    /**
     * @param $value
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $value,
        array $type,
        SerializationContext $context
    ): string {
        /** @var AbstractDomainObject $entity */
        $entity = $context->getObject();

        if (!$entity instanceof AbstractDomainObject) {
            throw new \InvalidArgumentException(
                sprintf('Object has to extend %s to build URI', AbstractDomainObject::class),
                1562229270419
            );
        }

        $url = $this->contentObjectRenderer->typoLink_URL([
            'parameter' => sprintf('t3://record?identifier=%s&uid=%s', $type['params'][0], $entity->getUid()),
        ]);
        return UrlService::forceAbsoluteUrl(
            $url,
            $context->getAttribute('TYPO3_SITE_URL')
        );
    }

}
