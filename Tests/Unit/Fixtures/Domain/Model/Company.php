<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

class Company extends AbstractEntry
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Address
     */
    protected $invoiceAddress;
}
