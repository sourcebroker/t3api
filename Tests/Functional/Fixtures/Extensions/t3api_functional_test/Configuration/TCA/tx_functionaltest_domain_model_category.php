<?php

$buildTca = require __DIR__ . '/../TcaFixtureBase.php';

return $buildTca(
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
