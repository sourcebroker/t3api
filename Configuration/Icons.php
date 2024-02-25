<?php

$iconList = [];
foreach ([
             'ext-t3api' => 'Extension.svg',
         ] as $identifier => $path) {
    $iconList[$identifier] = [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:t3api/Resources/Public/Icons/' . $path,
    ];
}

return $iconList;
