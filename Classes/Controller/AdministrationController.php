<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SourceBroker\T3api\Service\SiteService;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

#[Controller]
class AdministrationController
{
    public function __construct(
        protected readonly FlashMessageService $flashMessage,
        protected readonly UriBuilder $uriBuilder,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly SiteFinder $siteFinder,
        protected readonly FlashMessageService $flashMessageService,
        protected readonly PageRenderer $pageRenderer
    ) {
    }

    /**
     * @throws RouteNotFoundException
     */
    public function documentationAction(ServerRequestInterface $request): ResponseInterface
    {
        $view = $this->moduleTemplateFactory->create($request);
        $moduleData = $request->getAttribute('moduleData');
        $siteIdentifier = $request->getQueryParams()['site'] ?? $this->getDefaultSiteIdentifier($moduleData);
        /** @var ModuleData $moduleData */
        $moduleIdentifier = $request->getAttribute('module')->getIdentifier();

        try {
            $activeSite = SiteService::getByIdentifier($siteIdentifier);
        } catch (SiteNotFoundException $e) {
            $activeSite = null;
        }

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

    protected function getDefaultSiteIdentifier(ModuleData $moduleData): string
    {
        $sites = SiteService::getAll();
        $lastSelectedSiteIdentifier = $moduleData->get('lastSelectedSiteIdentifier');
        if ($lastSelectedSiteIdentifier !== null && $sites[$lastSelectedSiteIdentifier] instanceof Site) {
            return $sites[$lastSelectedSiteIdentifier]->getIdentifier();
        }

        return (SiteService::getCurrent() ?? array_shift($sites))->getIdentifier();
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
                $site->getIdentifier() . ($host ? ' (' . $host . ')' : '')
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
