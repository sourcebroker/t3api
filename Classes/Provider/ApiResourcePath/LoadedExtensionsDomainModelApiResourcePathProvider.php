<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Provider\ApiResourcePath;

use Generator;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class LoadedExtensionsDomainModelApiResourcePathProvider implements ApiResourcePathProvider
{
    public function getAll(): Generator
    {
        foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extKey) {
            $extPath = ExtensionManagementUtility::extPath($extKey);
            foreach (glob($extPath . 'Classes/Domain/Model/*.php') as $domainModelClassFile) {
                yield $domainModelClassFile;
            }
        }
    }
}
