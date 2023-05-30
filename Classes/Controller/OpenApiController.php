<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Controller;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException as OasInvalidArgumentException;
use ReflectionException;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Service\OpenApiBuilder;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class OpenApiController extends ActionController
{
    /**
     * @var ApiResourceRepository
     */
    protected $apiResourceRepository;

    /** @noinspection PhpUnused */
    public function injectApiResourceRepository(
        ApiResourceRepository $apiResourceRepository
    ): void {
        $this->apiResourceRepository = $apiResourceRepository;
    }

    /**
     * @param string $siteIdentifier
     * @throws SiteNotFoundException
     */
    public function displayAction(string $siteIdentifier): void
    {
        $this->view->assign(
            'specUrl',
            $this->uriBuilder->reset()->uriFor(
                'spec',
                ['siteIdentifier' => $siteIdentifier]
            )
        );
        $this->view->assign(
            'site',
            GeneralUtility::makeInstance(SiteFinder::class)
                ->getSiteByIdentifier($siteIdentifier)
        );
    }

    /**
     * @param string $siteIdentifier
     * @return string
     * @throws OasInvalidArgumentException
     * @throws ReflectionException
     * @throws SiteNotFoundException
     */
    public function specAction(string $siteIdentifier): string
    {
        $originalRequest = $GLOBALS['TYPO3_REQUEST'];
        $site = GeneralUtility::makeInstance(SiteFinder::class)
            ->getSiteByIdentifier($siteIdentifier);
        $imitateSiteRequest = $originalRequest->withAttribute('site', $site);
        $GLOBALS['TYPO3_REQUEST'] = $imitateSiteRequest;
        $output = OpenApiBuilder::build($this->apiResourceRepository->getAll())
            ->toJson();
        $this->writeSpecFile($siteIdentifier, $output);
        $GLOBALS['TYPO3_REQUEST'] = $originalRequest;

        return $output;
    }

    protected function writeSpecFile(string $siteIdentifier, string $output): void
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        try {
            $specFilePath = $extensionConfiguration->get('t3api', 'spec_files_path');
        } catch (ExtensionConfigurationPathDoesNotExistException|ExtensionConfigurationExtensionNotConfiguredException $e) {
            $specFilePath = '';
        }
        if ($specFilePath && is_string($specFilePath)) {
            GeneralUtility::writeFile($specFilePath . '/openapi_' . $siteIdentifier . '.json', $output);
        }
    }
}
