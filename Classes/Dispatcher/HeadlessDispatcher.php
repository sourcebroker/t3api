<?php

namespace V\Local\Service;

use SourceBroker\T3api\Dispatcher\AbstractDispatcher;
use TYPO3\CMS\Core\SingletonInterface;

class HeadlessDispatcher
    extends AbstractDispatcher
    implements SingletonInterface
{
}
