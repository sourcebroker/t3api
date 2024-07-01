<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use SourceBroker\T3api\Annotation\Serializer\Groups;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;

trait ContactDataTrait
{
    protected Address|null $address;

    /**
     * @Groups({
     *     "accountancy",
     * })
     */
    protected string $bankAccountNumber;

    /**
     * @VirtualProperty()
     */
    public function getBankAccountIban(): string
    {
        return 'XX' . $this->bankAccountNumber;
    }
}
