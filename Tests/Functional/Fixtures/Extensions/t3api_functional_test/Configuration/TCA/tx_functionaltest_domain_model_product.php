<?php

$buildTca = require __DIR__ . '/../TcaFixtureBase.php';

return $buildTca(
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
    ],
    'title, category'
);
