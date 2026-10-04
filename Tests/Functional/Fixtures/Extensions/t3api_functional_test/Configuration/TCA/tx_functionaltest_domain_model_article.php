<?php

use T3apiTests\FunctionalTest\Tca\TcaFixtureBuilder;

$tca = TcaFixtureBuilder::build(
    'Article',
    [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
        // Extbase maps only fields configured in TCA columns
        'crdate' => [
            'label' => 'Creation date',
            'config' => [
                'type' => 'datetime',
            ],
        ],
        'tstamp' => [
            'label' => 'Modification date',
            'config' => [
                'type' => 'datetime',
            ],
        ],
    ],
    'title'
);
$tca['ctrl']['crdate'] = 'crdate';
$tca['ctrl']['tstamp'] = 'tstamp';

return $tca;
