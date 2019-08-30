<?php

namespace V\Local\Service;

use Exception;
use SourceBroker\T3api\Dispatcher\AbstractDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;

class HeadlessDispatcher
    extends AbstractDispatcher
    implements SingletonInterface
{

    /**
     * @return string
     * @throws Exception
     * @throws RouteNotFoundException
     */
    public function process(string $route): string
    {
        $context = new RequestContext();
        $context->setPathInfo($route);

        return $this->processRequestByContext($context);
    }

}
