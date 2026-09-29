<?php

use T3apiTests\FunctionalTest\Tca\TcaFixtureBuilder;

return TcaFixtureBuilder::build(
    'Category',
    [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
    ],
    'title'
);
