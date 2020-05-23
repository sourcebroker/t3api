<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures;

class Address
{
    /**
     * @var string
     */
    protected $street;

    /**
     * @var string
     */
    protected $zip;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var \DateTimeImmutable
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $modified;
}
