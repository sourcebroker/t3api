<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

class Company extends AbstractEntry
{
    protected string $name;

    protected Address $invoiceAddress;
}
