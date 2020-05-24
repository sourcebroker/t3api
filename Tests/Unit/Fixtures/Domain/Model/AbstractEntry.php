<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use SourceBroker\T3api\Annotation\Serializer\ReadOnly;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

abstract class AbstractEntry implements TaggableInterface, IdentifiableInterface
{
    use ContactDataTrait;

    /**
     * @ReadOnly()
     * @var int
     */
    protected $id;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\Group>
     */
    protected $groups;

    /**
     * @var bool
     */
    protected $hidden;

    /**
     * @var ObjectStorage<Category>
     */
    protected $categories;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @VirtualProperty()
     * @return int[]
     */
    public function getTagIds()
    {
        return [122, 83, 110];
    }

    /**
     * @VirtualProperty("groupIds")
     * @return array<int>
     */
    public function getIdsOfAssignedGroups()
    {
        return [10, 27, 35];
    }

    /**
     * @VirtualProperty()
     * @return ObjectStorage<Tag>
     */
    public function getTags(): ObjectStorage
    {
        return new ObjectStorage();
    }
}
