<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures;

use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;

class Group implements IdentifiableInterface
{
    /**
     * @var string
     */
    protected $title;

    public function getId(): int
    {
        return 4;
    }

    /**
     * @VirtualProperty()
     * @return int
     */
    public function getNumberOfAssignedEntries(): int
    {
        return 89;
    }
}
