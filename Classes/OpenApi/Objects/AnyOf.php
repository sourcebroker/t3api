<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

class AnyOf extends SchemaComposition
{
    /**
     * @return string
     */
    protected function compositionType(): string
    {
        return 'anyOf';
    }
}
