<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use SourceBroker\T3api\Annotation\Serializer\SerializedName;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use SourceBroker\T3api\Tests\Unit\Fixtures\Annotation\Serializer\Type\ExampleTypeWithNestedParams;

class Person extends AbstractEntry
{
    protected string $firstName;

    protected string $lastName;

    /**
     * @SerializedName("familyName")
     */
    protected string $maidenName;

    protected \DateTime|null $dateOfBirth;

    protected \DateTimeImmutable $created;

    /**
     * @VirtualProperty
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @VirtualProperty("privateAddress")
     * @ExampleTypeWithNestedParams(
     *     "PrivateAddress",
     *     config={
     *         "parameter1": "value1",
     *         "parameter2": {
     *             "value2a",
     *             "value2b",
     *         },
     *         "parameter3": {
     *             "parameter3a": "value3a",
     *             "parameter3b": 3,
     *         },
     *     }
     * )
     */
    public function getPrivateAddress(): ?Address
    {
        return $this->address;
    }
}
