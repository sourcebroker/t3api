<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Provider\ApiResourcePath;

use SourceBroker\T3api\Attribute\AsApiResourcePathProvider;
use SourceBroker\T3api\Utility\FileUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

#[AsApiResourcePathProvider]
class LoadedExtensionsDomainModelApiResourcePathProvider implements ApiResourcePathProvider
{
    public function getAll(): iterable
    {
        foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extKey) {
            $extDomainModelPath = ExtensionManagementUtility::extPath($extKey) . 'Classes/Domain/Model/';
            foreach (FileUtility::getFilesRecursivelyByExtension($extDomainModelPath, 'php') as $domainModelClassFile) {
                yield $domainModelClassFile;
            }
        }
    }
}
