<?php

declare(strict_types=1);

namespace SourceBroker\T3api\ViewHelpers;

use SourceBroker\T3api\Dispatcher\HeadlessDispatcher;
use SourceBroker\T3api\Service\RouteService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class InlineViewHelper
 */
class InlineViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var HeadlessDispatcher
     */
    protected $headlessDispatcher;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->headlessDispatcher = GeneralUtility::makeInstance(HeadlessDispatcher::class);
    }

    /**
     * @inheritDoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('route', 'string', 'API endpoint route', true);
        $this->registerArgument('params', 'array', 'Request parameters', false, []);
        $this->registerArgument('itemsPerPage', 'int', 'Items per page number');
        $this->registerArgument('page', 'int', 'Pagination page number');
    }

    /**
     * @return string
     * @throws RouteNotFoundException
     */
    public function render(): string
    {
        $request = Request::create($this->getRequestUri(), 'GET', $this->getRequestParameters());
        $requestContext = (new RequestContext())->fromRequest($request);

        return $this->headlessDispatcher->processOperationByRequest($requestContext, $request);
    }

    /**
     * @return string
     */
    protected function getRequestUri(): string
    {
        return implode(
            '/',
            [
                GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'),
                RouteService::getFullApiBasePath(),
                $this->arguments['route'],
            ]
        );
    }

    /**
     * @return array
     */
    protected function getRequestParameters(): array
    {
        $params = $this->arguments['params'];

        if ($this->hasArgument('itemsPerPage')) {
            // todo add support for `items_per_page_parameter_name` specific for selected route
            $params[$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination']['items_per_page_parameter_name']] =
                $this->arguments['itemsPerPage'];
        }

        if ($this->hasArgument('page')) {
            // todo add support for `page_parameter_name` specific for selected route
            $params[$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['pagination']['page_parameter_name']] =
                $this->arguments['page'];
        }

        return $params;
    }
}
