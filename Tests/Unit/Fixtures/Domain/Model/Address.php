<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

class Address
{
    protected string $street;

    protected string $zip;

    protected string $city;

    protected \DateTimeImmutable $created;

    protected \DateTime $modified;
}
