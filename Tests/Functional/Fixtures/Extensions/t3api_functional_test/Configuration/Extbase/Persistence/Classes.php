<?php

declare(strict_types=1);

use T3apiTests\FunctionalTest\Domain\Model\CategoryResource;
use T3apiTests\FunctionalTest\Domain\Model\TagResource;

return [
    CategoryResource::class => [
        'tableName' => 'tx_functionaltest_domain_model_category',
    ],
    TagResource::class => [
        'tableName' => 'tx_functionaltest_domain_model_tag',
    ],
];
