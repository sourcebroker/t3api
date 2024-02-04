<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Controller;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SourceBroker\T3api\Service\SiteService;
use TYPO3\CMS\Backend\Template\Components\Menu\MenuItem;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Template\ModuleTemplate;

/**
 * TODO: finish refactoring for TYPO3 12
 */
class AdministrationController
{
    protected const SITE_SELECTOR_MENU_KEY = 'spec_site_selector_menu';

    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected mixed $extensionConfiguration;
    protected FlashMessageQueue $flashMessageQueue;
    private UriBuilder $uriBuilder;
    private ModuleTemplate $view;

    public function __construct(
        FlashMessageService $flashMessageService,
        ModuleTemplateFactory $moduleTemplateFactory,
        UriBuilder $uriBuilder
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $this->uriBuilder = $uriBuilder;
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->documentationAction($request);
    }

    protected function initializeView(ServerRequestInterface $request): ModuleTemplate
    {
        $this->module = $request->getAttribute('module');
        return $this->moduleTemplateFactory->create($request);
    }

    public function documentationAction($request, string $siteIdentifier = null): ResponseInterface
    {
        $this->view = $this->initializeView($request);
        $siteIdentifier = $siteIdentifier ?? $this->getDefaultSiteIdentifier();
        try {
            $site = SiteService::getByIdentifier($siteIdentifier);
        } catch (SiteNotFoundException $e) {
            $site = null;
        }
        $this->setUserModuleData('lastSelectedSiteIdentifier', $siteIdentifier);
        $this->generateSiteSelectorMenu();
        $this->setSelectedItemInSiteSelectorMenu($site);

        if (!SiteService::hasT3apiRouteEnhancer($site)) {
            $this->addFlashMessage(
                sprintf(
                    'T3api route enhancer is not defined for site `%s`. Check documentation to see how to properly install t3api extension.',
                    $site->getIdentifier()
                ),
                '',
                AbstractMessage::ERROR,
                false
            );

            return $this->view->renderResponse('Administration/Documentation');
        }

        $this->view->assign(
            'displayUrl',
            (string)$this->uriBuilder->buildUriFromRoute(
                $this->module->getIdentifier(),
                ['siteIdentifier' => $siteIdentifier],
                'OpenApi'
            )
        );

        return $this->view->renderResponse('Administration/Documentation');

    }

    protected function getDefaultSiteIdentifier(): string
    {
        $sites = SiteService::getAll();
        $lastSelectedSiteIdentifier = $this->getUserModuleData('lastSelectedSiteIdentifier');

        if ($lastSelectedSiteIdentifier !== null && $sites[$lastSelectedSiteIdentifier] instanceof Site) {
            return $sites[$lastSelectedSiteIdentifier]->getIdentifier();
        }

        return (SiteService::getCurrent() ?? array_shift($sites))->getIdentifier();
    }

    protected function generateSiteSelectorMenu(): void
    {
        $siteSelectorMenu = $this->view->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $siteSelectorMenu->setIdentifier(self::SITE_SELECTOR_MENU_KEY);
        foreach ($this->getSites() as $site) {
            $siteSelectorMenu->addMenuItem(
                $this->enrichSiteSelectorMenuItem(
                    $siteSelectorMenu->makeMenuItem(),
                    $site
                )
            );
        }
        $this->view->getDocHeaderComponent()->getMenuRegistry()->addMenu($siteSelectorMenu);
    }

    protected function enrichSiteSelectorMenuItem(
        MenuItem $menuItem,
        Site $site
    ): MenuItem {
        $host = $site->getBase()->getHost();
        $menuItem->setTitle(
            $site->getIdentifier() . ($host ? ' (' . $host . ')' : '')
        );
        $menuItem->setHref(
            (string)$this->uriBuilder->buildUriFromRoute(
                $this->module->getIdentifier(),
                ['siteIdentifier' => $site->getIdentifier()]
            )
        );
        $menuItem->setDataAttributes(['siteIdentifier' => $site->getIdentifier()]);

        return $menuItem;
    }

    protected function setSelectedItemInSiteSelectorMenu(Site $site): void
    {
        $menu = $this->view->getDocHeaderComponent()
            ->getMenuRegistry()
            ->getMenus()[self::SITE_SELECTOR_MENU_KEY];

        if (!$menu instanceof Menu) {
            throw new RuntimeException(
                sprintf(
                    'Menu `%s` is not registered',
                    self::SITE_SELECTOR_MENU_KEY
                ),
                1604259496549
            );
        }

        /** @var MenuItem $menuItem */
        foreach ($menu->getMenuItems() as $menuItem) {
            if ($menuItem->getDataAttributes()['siteIdentifier'] === $site->getIdentifier()) {
                $menuItem->setActive(true);
            }
        }
    }

    /**
     * @return Site[]
     */
    protected function getSites(): array
    {
        return GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();
    }

    protected function getUserModuleData(string $variable)
    {
        $moduleData = $GLOBALS['BE_USER']->getModuleData('tx_t3api_m1');
        return $moduleData[$variable] ?? null;
    }

    protected function setUserModuleData(string $variable, $value): void
    {
        $userModuleData = $GLOBALS['BE_USER']->getModuleData('tx_t3api_m1') ?? [];
        $userModuleData[$variable] = $value;
        $GLOBALS['BE_USER']->pushModuleData('tx_t3api_m1', $userModuleData);
    }
}
