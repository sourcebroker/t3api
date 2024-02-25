<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Controller;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException as OasInvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Service\OpenApiBuilder;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Site\SiteFinder;

#[Controller]
class OpenApiController
{
    public function __construct(
        protected readonly ApiResourceRepository $apiResourceRepository,
        protected readonly FlashMessageService $flashMessage,
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly SiteFinder $siteFinder,
    ) {}

    /**
     * @throws OasInvalidArgumentException
     * @throws SiteNotFoundException
     */
    public function resourcesAction(ServerRequestInterface $request): ResponseInterface
    {
        $siteIdentifier = $request->getQueryParams()['site'] ?? null;
        $site = $this->siteFinder->getSiteByIdentifier($siteIdentifier);

        $imitateSiteRequest = $request->withAttribute('site', $site);
        $GLOBALS['TYPO3_REQUEST'] = $imitateSiteRequest;
        $output = OpenApiBuilder::build($this->apiResourceRepository->getAll())->toJson();
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($output);

        return $response;
    }
}
