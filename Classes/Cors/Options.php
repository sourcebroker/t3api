<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Cors;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Options
{
    /**
     * List of simple methods
     *
     * @var array
     */
    protected $simpleMethods = [
        'GET',
        'HEAD',
        'POST',
    ];

    /**
     * List of simple headers
     *
     * @var array
     */
    protected $simpleHeaders = [
        'Accept',
        'Accept-Language',
        'Content-Language',
    ];

    /**
     * @var bool $allowCredentials
     */
    protected $allowCredentials = false;

    /**
     * @var array
     */
    protected $allowOrigin = [];

    /**
     * @var string
     */
    protected $allowOriginPattern = '';

    /**
     * @var array
     */
    protected $allowHeaders = [];

    /**
     * @var array
     */
    protected $allowMethods = [];

    /**
     * @var array
     */
    protected $exposeHeaders = [];

    /**
     * @var int
     */
    protected $maxAge;

    /**
     * @var bool
     */
    protected $empty = true;

    /**
     * Options constructor.
     */
    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configuration =
            $objectManager->get(ConfigurationManagerInterface::class)->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            )['config.']['tx_t3api.']['cors.'] ?: [];
        foreach ($configuration as $option => $value) {
            $internalValue = '';
            switch ($option) {
                case 'allowHeaders':
                case 'allowMethods':
                case 'allowOrigin':
                case 'exposeHeaders':
                    $internalValue = GeneralUtility::trimExplode(',', $value);
                    break;
                case 'allowCredentials':
                    $internalValue = in_array($value, ['1', 'true'], true);
                    break;
                case 'maxAge':
                    $internalValue = (int) $value;
                    break;
                case 'allowOrigin.':
                    $internalValue = $value['pattern'] ?: '';
                    $option = 'allowOriginPattern';
                    break;
            }
            if ($internalValue) {
                $this->empty = false;
                $this->$option = $internalValue;
            }
        }
    }

    /**
     * @return array
     */
    public function getSimpleMethods(): array
    {
        return $this->simpleMethods;
    }

    /**
     * @return array
     */
    public function getSimpleHeaders(): array
    {
        return $this->simpleHeaders;
    }

    /**
     * @return bool
     */
    public function isAllowCredentials(): bool
    {
        return $this->allowCredentials;
    }

    /**
     * @return array
     */
    public function getAllowOrigin(): array
    {
        return $this->allowOrigin;
    }

    /**
     * @return string
     */
    public function getAllowOriginPattern(): string
    {
        return $this->allowOriginPattern;
    }

    /**
     * @return array
     */
    public function getAllowHeaders(): array
    {
        return $this->allowHeaders;
    }

    /**
     * @return array
     */
    public function getAllowMethods(): array
    {
        return $this->allowMethods;
    }

    /**
     * @return array
     */
    public function getExposeHeaders(): array
    {
        return $this->exposeHeaders;
    }

    /**
     * @return int
     */
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return !$this->empty;
    }
}
