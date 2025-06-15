<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Dispatcher;

use TYPO3\CMS\Core\SingletonInterface;

class HeadlessDispatcher extends AbstractDispatcher implements SingletonInterface
{
    protected function init(): void
    {
        // @TODO This comment is here just for reformatting compatibility of csfixes and phpstorm.
    }
}
