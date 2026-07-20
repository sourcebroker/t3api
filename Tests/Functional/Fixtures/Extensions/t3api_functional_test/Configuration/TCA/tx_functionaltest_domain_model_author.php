<?php

$buildTca = require __DIR__ . '/../TcaFixtureBase.php';

return $buildTca(
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
