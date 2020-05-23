<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Tests\Unit\Fixtures;

use SourceBroker\T3api\Annotation\Serializer\Groups;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;

trait ContactDataTrait
{
    /**
     * @var \SourceBroker\T3api\Tests\Unit\Fixtures\Address|null
     */
    protected $address;

    /**
     * @var string
     * @Groups({
     *     "accountancy",
     * })
     */
    protected $bankAccountNumber;

    /**
     * @VirtualProperty()
     * @return string
     */
    public function getBankAccountIban(): string
    {
        return 'XX' . $this->bankAccountNumber;
    }
}
