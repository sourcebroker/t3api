<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures;

use SourceBroker\T3api\Annotation\Serializer\SerializedName;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;

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
     * @var \DateTimeImmutable
     */
    protected $created;

    /**
     * @VirtualProperty()
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
