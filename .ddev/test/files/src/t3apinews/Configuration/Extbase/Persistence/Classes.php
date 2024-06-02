<?php

declare(strict_types=1);

use SourceBroker\T3apinews\Domain\Model\News;
use SourceBroker\T3apinews\Domain\Model\Tag;
use SourceBroker\T3apinews\Domain\Model\FileReference;
use SourceBroker\T3apinews\Domain\Model\Category;

return [
    News::class => [
        'tableName' => 'tx_news_domain_model_news',
    ],
    Tag::class => [
        'tableName' => 'tx_news_domain_model_tag',
    ],
    FileReference::class => [
        'tableName' => 'sys_file_reference',
    ],
    Category::class => [
        'tableName' => 'sys_category',
    ],
];
