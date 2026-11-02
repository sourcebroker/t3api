<?php

use T3apiTests\FunctionalTest\Tca\TcaFixtureBuilder;

return TcaFixtureBuilder::build(
    'Recipe',
    [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'steps' => [
            'label' => 'Steps',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_functionaltest_domain_model_recipestep',
                'foreign_field' => 'recipe',
            ],
        ],
        'images' => [
            'label' => 'Images',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
            ],
        ],
    ],
    'title, steps, images'
);
