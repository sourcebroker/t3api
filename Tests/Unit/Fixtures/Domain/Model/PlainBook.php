<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class PlainBook extends AbstractEntity
{
    protected string $title = '';
}
