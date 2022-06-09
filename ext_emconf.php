<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'T3api',
    'description' => 'REST API for your TYPO3 project. Config with annotations, build in filtering, pagination, typolinks, image processing, serialization contexts, responses in Hydra/JSON-LD format.',
    'category' => 'plugin',
    'author' => 'SourceBroker Team',
    'author_email' => 'office@sourcebroker.dev',
    'state' => 'stable',
    'version' => '1.2.3',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-8.0.99',
            'typo3' => '10.4.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
