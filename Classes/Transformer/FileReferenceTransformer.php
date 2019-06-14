<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

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
     *
     * @return array
     */
    public function serialize($fileReference)
    {
        return [
            'uid' => $fileReference->getUid(),
            'url' => $fileReference->getOriginalResource()->getPublicUrl(),
        ];
    }
}
