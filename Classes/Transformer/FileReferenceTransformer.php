<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Class FileReferenceTransformer
 */
class FileReferenceTransformer extends AbstractTransformer
{
    /**
     * @param FileReference $fileReference
     *
     * @return array
     */
    public function serialize($fileReference)
    {
        return [
            'uid' => $fileReference->getUid(),
            'url' => GeneralUtility::getIndpEnv('TYPO3_SITE_URL')
                . $fileReference->getOriginalResource()->getPublicUrl(),
        ];
    }
}
