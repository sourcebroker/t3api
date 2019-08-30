<?php
declare(strict_types=1);

namespace SourceBroker\T3api\ViewHelpers;

use SourceBroker\T3api\Dispatcher\HeadlessDispatcher;
use Symfony\Component\Routing\RequestContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class InitialDataViewHelper
    extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var HeadlessDispatcher
     */
    protected $headlessDispatcher;

    public function initialize()
    {
        parent::initialize();

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->headlessDispatcher = $objectManager->get(HeadlessDispatcher::class);
    }

    public function initializeArguments()
    {
        $this->registerArgument('route', 'string', 'API endpoint route');
        $this->registerArgument('itemsPerPage', 'int', 'Items per page number');
        $this->registerArgument('params', 'array', 'API endpoint route');
    }

    public function render()
    {
        $requestContext = new RequestContext();

        $fullPath = '/_api/' . $this->arguments['route'];
        $requestContext->setPathInfo($fullPath);

        if ($this->hasArgument('params')) {
            $requestContext->setParameters($this->arguments['params']);
        }

        if ($this->hasArgument('itemsPerPage')) {
            $requestContext->setParameter('itemsPerPage', $this->arguments['itemsPerPage']);
        }

        // request
        $dataJson = null;

        try {
            $dataJson = $this->headlessDispatcher->processOperationByContext($requestContext);
        } catch (\Exception $e) {
        }

        return json_decode($dataJson, true);
    }

}
