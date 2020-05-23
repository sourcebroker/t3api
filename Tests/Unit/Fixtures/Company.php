<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures;

class Company extends AbstractEntry
{
    /**
     * @var string
     */
    protected $name;

//    @todo enable later - should work correctly with Symfony/PropertyInfo
    /**
     * @var Address
     */
    protected $invoiceAddress;
}
