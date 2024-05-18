<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\Serializer\Type;

interface TypeInterface
{
    public function getParams(): array;

    public function getName(): string;
}
