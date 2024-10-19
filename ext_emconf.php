<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'T3api',
    'description' => 'REST API for your TYPO3 project. Config with annotations, build in filtering, pagination, typolinks, image processing, serialization contexts, responses in Hydra/JSON-LD format.',
    'category' => 'plugin',
    'author' => 'Inscript Team',
    'author_email' => 'office@inscript.dev',
    'state' => 'stable',
    'version' => '4.0.1',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.3.99',
            'typo3' => '12.4.00-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
