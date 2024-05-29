<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;

class Group implements IdentifiableInterface
{
    protected string $title;

    public function getId(): int
    {
        return 4;
    }

    /**
     * @VirtualProperty
     */
    public function getNumberOfAssignedEntries(): int
    {
        return 89;
    }
}
