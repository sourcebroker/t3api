<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use SourceBroker\T3api\Annotation\ApiResource;
use SourceBroker\T3api\Annotation as T3api;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Resource with relations stored in the related table: inline `steps` (`foreign_field`) and `images` (TCA `file`),
 * and with files (`sys_file`, not references) in `logo` (API resource class) and `logos` (Extbase's `File` class).
 *
 * @ApiResource(
 *     collectionOperations={
 *         "post": {
 *             "method": "POST",
 *             "path": "/recipes",
 *             "normalizationContext": {
 *                 "groups": {"api_recipe"}
 *             },
 *             "denormalizationContext": {
 *                 "groups": {"api_recipe_write"}
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/recipes/{id}",
 *             "normalizationContext": {
 *                 "groups": {"api_recipe"}
 *             },
 *             "denormalizationContext": {
 *                 "groups": {"api_recipe_write"}
 *             }
 *         },
 *         "patch": {
 *             "method": "PATCH",
 *             "path": "/recipes/{id}",
 *             "normalizationContext": {
 *                 "groups": {"api_recipe"}
 *             },
 *             "denormalizationContext": {
 *                 "groups": {"api_recipe_write"}
 *             }
 *         },
 *         "put": {
 *             "method": "PUT",
 *             "path": "/recipes/{id}",
 *             "normalizationContext": {
 *                 "groups": {"api_recipe"}
 *             },
 *             "denormalizationContext": {
 *                 "groups": {"api_recipe_write"}
 *             }
 *         }
 *     }
 * )
 */
class Recipe extends AbstractEntity
{
    protected string $title = '';

    /**
     * @var ObjectStorage<RecipeStep>
     * @T3api\ORM\Cascade("persist")
     */
    protected ObjectStorage $steps;

    /**
     * @T3api\ORM\Cascade("persist")
     */
    protected ?Category $category = null;

    /**
     * @var ObjectStorage<Tag>
     * @T3api\ORM\Cascade("persist")
     */
    protected ObjectStorage $tags;

    protected ?FileReference $image = null;

    /**
     * @var ObjectStorage<FileReference>
     */
    protected ObjectStorage $images;

    protected ?File $logo = null;

    /**
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\File>
     */
    protected ObjectStorage $logos;

    public function __construct()
    {
        $this->steps = new ObjectStorage();
        $this->tags = new ObjectStorage();
        $this->images = new ObjectStorage();
        $this->logos = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return ObjectStorage<RecipeStep>
     */
    public function getSteps(): ObjectStorage
    {
        return $this->steps;
    }

    /**
     * @param ObjectStorage<RecipeStep> $steps
     */
    public function setSteps(ObjectStorage $steps): void
    {
        $this->steps = $steps;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    /**
     * @return ObjectStorage<Tag>
     */
    public function getTags(): ObjectStorage
    {
        return $this->tags;
    }

    /**
     * @param ObjectStorage<Tag> $tags
     */
    public function setTags(ObjectStorage $tags): void
    {
        $this->tags = $tags;
    }

    public function getImage(): ?FileReference
    {
        return $this->image;
    }

    public function setImage(?FileReference $image): void
    {
        $this->image = $image;
    }

    /**
     * @return ObjectStorage<FileReference>
     */
    public function getImages(): ObjectStorage
    {
        return $this->images;
    }

    /**
     * @param ObjectStorage<FileReference> $images
     */
    public function setImages(ObjectStorage $images): void
    {
        $this->images = $images;
    }

    public function getLogo(): ?File
    {
        return $this->logo;
    }

    public function setLogo(?File $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @return ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\File>
     */
    public function getLogos(): ObjectStorage
    {
        return $this->logos;
    }

    /**
     * @param ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\File> $logos
     */
    public function setLogos(ObjectStorage $logos): void
    {
        $this->logos = $logos;
    }
}
