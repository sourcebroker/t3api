<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer\Type;

/**
 * Interface TypeInterface
 */
interface TypeInterface
{
    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @return string
     */
    public function getName(): string;
}
