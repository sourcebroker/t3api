<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Controller;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException as OasInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Service\OpenApiBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * TODO: finish refactoring for TYPO3 12
 */
class OpenApiController extends ActionController
{
    /**
     * @var ApiResourceRepository
     */
    protected $apiResourceRepository;

    public function __construct(ApiResourceRepository $apiResourceRepository)
    {
        $this->apiResourceRepository = $apiResourceRepository;
    }

    /**
     * @param string $siteIdentifier
     * @throws SiteNotFoundException
     */
    public function displayAction(string $siteIdentifier): ResponseInterface
    {
        $this->view->assign(
            'specUrl',
            $this->uriBuilder->reset()->uriFor('spec', ['siteIdentifier' => $siteIdentifier])
        );
        $this->view->assign(
            'site',
            GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier)
        );
        return $this->htmlResponse();
    }

    /**
     * @param string $siteIdentifier
     * @return string
     * @throws OasInvalidArgumentException
     * @throws ReflectionException
     * @throws SiteNotFoundException
     */
    public function specAction(string $siteIdentifier): ResponseInterface
    {
        $originalRequest = $GLOBALS['TYPO3_REQUEST'];
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        $imitateSiteRequest = $originalRequest->withAttribute('site', $site);
        $GLOBALS['TYPO3_REQUEST'] = $imitateSiteRequest;
        $output = OpenApiBuilder::build($this->apiResourceRepository->getAll())->toJson();
        $GLOBALS['TYPO3_REQUEST'] = $originalRequest;

        return $this->htmlResponse($output);
    }
}
