<?php

return [
    'ctrl' => [
        'title' => 'Book',
        'label' => 'title',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'author' => [
            'label' => 'Author',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_functionaltest_domain_model_author',
                'foreign_table_where' => 'ORDER BY tx_functionaltest_domain_model_author.name ASC',
                'default' => 0,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title, author'],
    ],
];
