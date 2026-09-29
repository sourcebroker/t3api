<?php

use T3apiTests\FunctionalTest\Tca\TcaFixtureBuilder;

return TcaFixtureBuilder::build(
    'Product',
    [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
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
                'MM' => 'tx_functionaltest_product_tag_mm',
            ],
        ],
    ],
    'title, category, tags'
);
