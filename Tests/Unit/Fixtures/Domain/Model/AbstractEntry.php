<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use SourceBroker\T3api\Annotation\Serializer\ReadOnlyProperty;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

abstract class AbstractEntry implements TaggableInterface, IdentifiableInterface
{
    use ContactDataTrait;

    /**
     * @ReadOnlyProperty
     */
    protected int $id;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Group>
     */
    protected ObjectStorage $groups;

    protected bool $hidden;

    /**
     * @var ObjectStorage<Category>
     */
    protected ObjectStorage $categories;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @VirtualProperty
     * @return int[]
     */
    public function getTagIds(): array
    {
        return [122, 83, 110];
    }

    /**
     * @VirtualProperty("groupIds")
     * @return array<int>
     */
    public function getIdsOfAssignedGroups(): array
    {
        return [10, 27, 35];
    }

    /**
     * @VirtualProperty
     * @return ObjectStorage<Tag>
     */
    public function getTags(): ObjectStorage
    {
        return new ObjectStorage();
    }
}
