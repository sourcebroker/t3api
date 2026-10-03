<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SourceBroker\T3api\Service\SiteService;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

#[AsController]
class AdministrationController
{
    public function __construct(
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly SiteFinder $siteFinder,
        protected readonly PageRenderer $pageRenderer
    ) {}

    /**
     * @throws RouteNotFoundException
     */
    public function documentationAction(ServerRequestInterface $request): ResponseInterface
    {
        $view = $this->moduleTemplateFactory->create($request);
        /** @var ModuleData $moduleData */
        $moduleData = $request->getAttribute('moduleData');
        $moduleIdentifier = $request->getAttribute('module')->getIdentifier();

        $activeSite = $this->getActiveSite($request->getQueryParams()['site'] ?? null, $moduleData);
        if ($activeSite === null) {
            $view->addFlashMessage(
                'No site configuration found. T3api requires at least one site with the T3api route enhancer.',
                'T3api',
                ContextualFeedbackSeverity::ERROR,
                false
            );

            return $view->renderResponse('Administration/Documentation');
        }
        $siteIdentifier = $activeSite->getIdentifier();

        $moduleData->set('lastSelectedSiteIdentifier', $siteIdentifier);
        $this->getBackendUser()->pushModuleData($moduleData->getModuleIdentifier(), $moduleData->toArray());

        $siteSelectorMenu = $view->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $siteSelectorMenu->setIdentifier($moduleIdentifier);
        $this->generateSiteSelectorMenuItems($activeSite, $siteSelectorMenu, $moduleIdentifier);
        $view->getDocHeaderComponent()->getMenuRegistry()->addMenu($siteSelectorMenu);

        if (!SiteService::hasT3apiRouteEnhancer($activeSite)) {
            $view->addFlashMessage(
                sprintf(
                    'T3api route enhancer is not defined for site `%s`. Check documentation to see how to properly install t3api extension.',
                    $activeSite->getIdentifier()
                ),
                'T3api route',
                ContextualFeedbackSeverity::ERROR,
                false
            );

            return $view->renderResponse('Administration/Documentation');
        }

        $this->pageRenderer->addCssFile('EXT:t3api/Resources/Public/Css/swagger-ui.css');
        $this->pageRenderer->addCssFile('EXT:t3api/Resources/Public/Css/swagger-custom.css');
        $this->pageRenderer->addJsFile('EXT:t3api/Resources/Public/JavaScript/swagger-ui-bundle.js');
        $this->pageRenderer->addJsFile('EXT:t3api/Resources/Public/JavaScript/swagger-ui-standalone-preset.js');
        $this->pageRenderer->loadJavaScriptModule('@sourcebroker/t3api/swagger-init.js');

        $view->assign(
            'resourcesUrl',
            $this->uriBuilder->buildUriFromRoute($moduleIdentifier . '.open_api_resources', ['site' => $siteIdentifier])
        );

        return $view->renderResponse('Administration/Documentation');
    }

    /**
     * Resolves the site to display: the requested one, then the last selected one, then the current one,
     * then the first configured one. Identifiers of sites which no longer exist are ignored.
     */
    protected function getActiveSite(?string $requestedSiteIdentifier, ModuleData $moduleData): ?Site
    {
        $sites = SiteService::getAll();
        foreach ([$requestedSiteIdentifier, $moduleData->get('lastSelectedSiteIdentifier')] as $siteIdentifier) {
            if (is_string($siteIdentifier) && ($sites[$siteIdentifier] ?? null) instanceof Site) {
                return $sites[$siteIdentifier];
            }
        }

        try {
            return SiteService::getCurrent();
        } catch (\RuntimeException) {
            return array_shift($sites);
        }
    }

    /**
     * @throws RouteNotFoundException
     */
    protected function generateSiteSelectorMenuItems(
        Site $activeSite,
        Menu $siteSelectorMenu,
        string $moduleIdentifier
    ): void {
        foreach ($this->siteFinder->getAllSites() as $site) {
            $menuItem = $siteSelectorMenu->makeMenuItem();
            $host = $site->getBase()->getHost();
            $menuItem->setTitle(
                $site->getIdentifier() . ($host !== '' ? ' (' . $host . ')' : '')
            );
            $menuItem->setHref(
                (string)$this->uriBuilder->buildUriFromRoute(
                    $moduleIdentifier,
                    ['site' => $site->getIdentifier()]
                )
            );
            if ($activeSite->getIdentifier() === $site->getIdentifier()) {
                $menuItem->setActive(true);
            }
            $siteSelectorMenu->addMenuItem($menuItem);
        }
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
