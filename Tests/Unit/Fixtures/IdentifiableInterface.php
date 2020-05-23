<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures;

interface IdentifiableInterface
{
    /**
     * @return int
     */
    public function getId(): int;
}
