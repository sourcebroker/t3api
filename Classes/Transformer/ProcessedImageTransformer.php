<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class FileReferenceTransformer
 *
 * @package SourceBroker\Restify\Transformator
 */
class ProcessedImageTransformer implements TransformerInterface
{
    const TYPE_NAME = 'ProcessedImage';

    /**
     * @param FileReference $fileReference
     * @param array $params
     *
     * @todo implement support for other image processing instructions?
     *
     * @return string
     */
    public function serialize($fileReference, ...$params)
    {
        $file = $fileReference->getOriginalResource()->getOriginalFile();
        $file = $file->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
            'width' => $params[0],
            'height' => $params[1],
        ]);

        return GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $file->getPublicUrl();
    }
}
