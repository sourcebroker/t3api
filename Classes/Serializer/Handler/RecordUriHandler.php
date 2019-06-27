<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use SourceBroker\Restify\Utility\TSConfig;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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

        $url = rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/');

        $parameter = $type['params'][1] ?? null;
        if ($parameter) {
            if (!is_numeric($parameter)) {
                $parameter = $this->getParameterFromConfig($parameter);
            }

            $url .= $this->getContentObjectRenderer()->getTypoLink_URL(sprintf(
                't3://record?identifier=%s&parameter=%u&uid=%s',
                $type['params'][0],
                $parameter,
                $uid
            ));
        } else {
            $url .= $this->getContentObjectRenderer()->getTypoLink_URL(sprintf(
                't3://record?identifier=%s&uid=%s',
                $type['params'][0],
                $uid
            ));
        }

        return $url;
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

    protected function getParameterFromConfig($configPath)
    {
        static $tsConfig;

        if (!$tsConfig instanceof TSConfig) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $tsConfig = $objectManager->get(TSConfig::class);
        }

        return (int) $tsConfig->getValue($configPath);
    }
}
