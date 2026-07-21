<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Category extends AbstractEntity
{
    protected string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }
}
