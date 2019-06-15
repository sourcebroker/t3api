<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\SerializationContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class RecordUriTransformer
 */
class RecordUriTransformer extends AbstractTransformer
{
    const TYPE_NAME = 'RecordUri';

    /**
     * @var string
     */
    protected $linkHandlerIdentifier = '';

    /**
     * RecordUriAbstractTransformer constructor.
     *
     * @param JsonSerializationVisitor $visitor
     * @param SerializationContext $context
     * @param $typeParams
     */
    public function __construct(JsonSerializationVisitor $visitor, SerializationContext $context, $typeParams)
    {
        parent::__construct($visitor, $context, $typeParams);
        $this->linkHandlerIdentifier = $typeParams[0] ?? $this->linkHandlerIdentifier;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $uid = null;

        foreach ($this->context->getVisitingSet() as $item) {
            if ($item instanceof AbstractEntity) {
                $uid = $item->getUid();
            }
        };

        if (!$uid) {
            return '';
        }

        return rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/')
            . $this->getContentObjectRenderer()->getTypoLink_URL(sprintf(
                't3://record?identifier=%s&uid=%s',
                $this->linkHandlerIdentifier,
                $uid
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
