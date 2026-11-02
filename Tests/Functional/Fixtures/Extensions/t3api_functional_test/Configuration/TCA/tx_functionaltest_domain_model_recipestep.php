<?php

use T3apiTests\FunctionalTest\Tca\TcaFixtureBuilder;

return TcaFixtureBuilder::build(
    'Recipe step',
    [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'recipe' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
    'title'
);
