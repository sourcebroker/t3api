<?php

declare(strict_types=1);

namespace T3apiTests\FunctionalTest\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Deliberately carries no `@ApiResource` annotation - it only ever appears embedded in `Book`'s
 * serialized output, which is the point of the nested-relation cache tag test: automatic
 * per-record tagging must not depend on the related entity being a resource in its own right.
 */
class Author extends AbstractEntity
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }
}
