<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class ProcessedImageTransformer
 */
class ProcessedImageTransformer extends AbstractTransformer
{
    const TYPE_NAME = 'ProcessedImage';

    /**
     * @var string|int
     */
    protected $width = '';

    /**
     * @var string|int
     */
    protected $height = '';

    /**
     * ProcessedImageAbstractTransformer constructor.
     *
     * @param JsonSerializationVisitor $visitor
     * @param Context $context
     * @param $typeParams
     */
    public function __construct(JsonSerializationVisitor $visitor, Context $context, $typeParams)
    {
        parent::__construct($visitor, $context, $typeParams);
        $this->width = $this->typeParams[0] ?? $this->width;
        $this->height = $this->typeParams[1] ?? $this->height;
    }

    /**
     * @param FileReference $fileReference
     *
     * @todo implement support for other image processing instructions?
     *
     * @return string
     */
    public function serialize($fileReference)
    {
        $file = $fileReference->getOriginalResource()->getOriginalFile();
        $file = $file->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
            'width' => $this->width,
            'height' => $this->height,
        ]);

        return GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $file->getPublicUrl();
    }
}
