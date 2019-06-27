<?php

namespace SourceBroker\Restify\Utility;

use TYPO3\CMS\Core\SingletonInterface;

class TSConfig implements SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager
     * @inject
     */
    protected $feConfigurationManager;

    /**
     * @var array
     */
    protected $fetched = [];

    /**
     * @param string $path
     * @return mixed
     */
    public function getValue(string $path)
    {
        if (!isset($this->fetched[$path])) {
            $extConfig = $this->feConfigurationManager->getTypoScriptSetup();
            $this->fetched[$path] = $this->getValueTraverse($extConfig, $path);
        }

        return $this->fetched[$path];
    }

    /**
     * @param $extConfig
     * @param string $path
     * @return mixed
     */
    protected function getValueTraverse($extConfig, string $path)
    {
        $dotPos = strpos($path, '.');
        if ($dotPos === false) {
            return $extConfig[$path] ?? null;
        }

        $key = substr($path, 0, $dotPos + 1);
        if (!is_array($extConfig[$key])) {
            return null;
        }

        $path = substr($path, $dotPos + 1);

        return $this->getValueTraverse($extConfig[$key], $path);
    }

}
