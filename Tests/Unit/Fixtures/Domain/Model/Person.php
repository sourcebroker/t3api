<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use DateTimeImmutable;
use SourceBroker\T3api\Annotation\Serializer\SerializedName;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use SourceBroker\T3api\Tests\Unit\Fixtures\Annotation\Serializer\Type\ExampleTypeWithNestedParams;

class Person extends AbstractEntry
{
    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @SerializedName("familyName")
     * @var string
     */
    protected $maidenName;

    /**
     * @var \DateTime|null
     */
    protected $dateOfBirth;

    /**
     * @var DateTimeImmutable
     */
    protected $created;

    /**
     * @VirtualProperty
     * @return string
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
