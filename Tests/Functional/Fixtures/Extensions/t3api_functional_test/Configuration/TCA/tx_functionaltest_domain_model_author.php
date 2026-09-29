<?php

use T3apiTests\FunctionalTest\Tca\TcaFixtureBuilder;

return TcaFixtureBuilder::build(
    'Author',
    [
        'name' => [
            'label' => 'Name',
            'config' => [
                'type' => 'input',
            ],
        ],
    ],
    'name',
    'name'
);
