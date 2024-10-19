<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Attribute;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsApiResourcePathProvider extends Autoconfigure
{
    public const TAG_NAME = 't3api.api_resource_path_provider';
    public function __construct(int $priority = 0)
    {
        parent::__construct(
            tags: [
                [self::TAG_NAME => ['priority' => $priority]],
            ]
        );
    }
}
