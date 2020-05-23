<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures;

class Tag implements IdentifiableInterface
{
    /**
     * @var string
     */
    public $title;

    public function getId(): int
    {
        return 15;
    }
}
