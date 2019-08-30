<?php
declare(strict_types=1);

namespace SourceBroker\T3api\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use V\Local\Service\HeadlessDispatcher;

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
    }

    public function render()
    {
        $dataJson = null;

        try {
            $dataJson = $this->headlessDispatcher->process($this->arguments['route']);
        } catch (\Exception $e) {
        }

        return $dataJson;
    }

}
