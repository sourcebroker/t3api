<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class FileReferenceTransformer
 *
 * @package SourceBroker\Restify\Transformator
 */
class FileReferenceTransformer implements TransformerInterface
{
    /**
     * @param FileReference $fileReference
     * @param array $params
     *
     * @return array
     */
    public function serialize($fileReference, ...$params)
    {
        return [
            'uid' => $fileReference->getUid(),
            'url' => GeneralUtility::getIndpEnv('TYPO3_SITE_URL')
                . $fileReference->getOriginalResource()->getPublicUrl(),
        ];
    }
}
