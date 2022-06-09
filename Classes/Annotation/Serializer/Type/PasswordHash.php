<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer\Type;

use SourceBroker\T3api\Serializer\Handler\PasswordHashHandler;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class PasswordHash implements TypeInterface
{
    public function getParams(): array
    {
        return [];
    }

    public function getName(): string
    {
        return PasswordHashHandler::TYPE;
    }
}
