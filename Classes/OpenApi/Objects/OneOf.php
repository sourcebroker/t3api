<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

class OneOf extends SchemaComposition
{
    /**
     * @return string
     */
    protected function compositionType(): string
    {
        return 'oneOf';
    }
}
