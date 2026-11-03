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
        'category' => [
            'label' => 'Category',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_functionaltest_domain_model_category',
                'default' => 0,
            ],
        ],
        'tags' => [
            'label' => 'Tags',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_functionaltest_domain_model_tag',
                'MM' => 'tx_functionaltest_recipe_tag_mm',
            ],
        ],
        'image' => [
            'label' => 'Image',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => 'common-image-types',
            ],
        ],
        'images' => [
            'label' => 'Images',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
            ],
        ],
        'logo' => [
            'label' => 'Logo',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_file',
                'default' => 0,
            ],
        ],
        'logos' => [
            'label' => 'Logos',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'sys_file',
                'MM' => 'tx_functionaltest_recipe_logo_mm',
            ],
        ],
    ],
    'title, steps, category, tags, image, images, logo, logos'
);
