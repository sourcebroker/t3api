<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer\Type;

use SourceBroker\T3api\Serializer\Handler\RecordUriHandler;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class RecordUri implements TypeInterface
{
    /**
     * @var string
     * @Required
     */
    public $identifier;

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [$this->identifier];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return RecordUriHandler::TYPE;
    }
}
