<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer\Type;

use InvalidArgumentException;
use SourceBroker\T3api\Serializer\Handler\CurrentFeUserHandler;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class CurrentFeUser implements TypeInterface
{
    /**
     * @var string
     */
    protected $feUserClass;

    public function __construct($options = [])
    {
        if (!is_string($options['value'] ?? null)) {
            throw new InvalidArgumentException(
                sprintf(
                    '`%s` Annotation needs a value representing the fe user class.',
                    self::class
                ),
                1609190297409
            );
        }

        $feUserClass = $options['value'];

        if (!class_exists($feUserClass)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class `%s` which should represent the fe user for `%s` does not exist.',
                    $feUserClass,
                    self::class
                ),
                1609190501468
            );
        }

        $this->feUserClass = $options['value'];
    }

    public function getParams(): array
    {
        return [$this->feUserClass];
    }

    public function getName(): string
    {
        return CurrentFeUserHandler::TYPE;
    }
}
