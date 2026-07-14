<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Factory;

use TYPO3\CMS\Core\Utility\GeneralUtility;

trait GetMergedTrait
{
    private function getInstances(\Traversable $instances, array $names): array
    {
        return array_merge(
            iterator_to_array($instances),
            array_map(
                static fn(string $className) => GeneralUtility::makeInstance($className),
                $names
            )
        );
    }
}
