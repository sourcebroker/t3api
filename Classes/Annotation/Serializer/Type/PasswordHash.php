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
    /**
     * Return stored password hash on serialization. By default `null` is returned.
     */
    public bool $serialize = false;

    public function getParams(): array
    {
        return $this->serialize ? [PasswordHashHandler::PARAM_SERIALIZE] : [];
    }

    public function getName(): string
    {
        return PasswordHashHandler::TYPE;
    }
}
