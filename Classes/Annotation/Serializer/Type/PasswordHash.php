<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer\Type;

use RuntimeException;
use SourceBroker\T3api\Serializer\Handler\PasswordHashHandler;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class PasswordHash implements TypeInterface
{
    public function __construct()
    {
        if (version_compare(TYPO3_branch, '9.4', '<')) {
            throw new RuntimeException('PasswordHash type is not supported for TYPO3 version lower than 9.4', 1614594542486);
        }
    }

    public function getParams(): array
    {
        return [];
    }

    public function getName(): string
    {
        return PasswordHashHandler::TYPE;
    }
}
