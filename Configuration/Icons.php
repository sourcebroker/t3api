<?php

use TYPO3\CMS\Core\Information\Typo3Version;

return [
    'ext-t3api' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:t3api/Resources/Public/Icons/Extension.svg',
    ],
    // TYPO3 v14 renders module icons inline as monochrome glyphs with an accent color. Older versions
    // still use colored tiles, so they keep the extension icon. Drop the condition with TYPO3 v13 support.
    'module-t3api' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => (new Typo3Version())->getMajorVersion() >= 14
            ? 'EXT:t3api/Resources/Public/Icons/Module.svg'
            : 'EXT:t3api/Resources/Public/Icons/Extension.svg',
    ],
];
