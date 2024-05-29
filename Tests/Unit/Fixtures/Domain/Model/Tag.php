<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

class Tag implements IdentifiableInterface
{
    public string $title;

    public function getId(): int
    {
        return 15;
    }
}
