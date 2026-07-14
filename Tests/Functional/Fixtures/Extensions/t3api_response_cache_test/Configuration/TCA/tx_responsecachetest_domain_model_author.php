<?php

return [
    'ctrl' => [
        'title' => 'Author',
        'label' => 'name',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
    ],
    'columns' => [
        'name' => [
            'label' => 'Name',
            'config' => [
                'type' => 'input',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'name'],
    ],
];
