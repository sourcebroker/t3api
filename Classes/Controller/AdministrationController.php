<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Controller;

use RuntimeException;
use SourceBroker\T3api\Service\SiteService;
use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3\CMS\Backend\Template\Components\Menu\MenuItem;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Controller\AbstractModuleController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class AdministrationController extends AbstractModuleController
{
    protected const SITE_SELECTOR_MENU_KEY = 'spec_site_selector_menu';

    /**
     * BackendTemplateView Container
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
    }

    public function documentationAction(string $siteIdentifier = null): void
    {
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
            return;
        }

        $this->view->assign(
            'displayUrl',
            $this->uriBuilder->reset()->uriFor(
                'display',
                ['siteIdentifier' => $siteIdentifier],
                'OpenApi'
            )
        );
    }

    protected function getDefaultSiteIdentifier(): string
    {
        $sites = SiteService::getAll();
        $lastSelectedSiteIdentifier
            = $this->getUserModuleData('lastSelectedSiteIdentifier');

        if ($lastSelectedSiteIdentifier !== null && $sites[$lastSelectedSiteIdentifier] instanceof Site) {
            return $sites[$lastSelectedSiteIdentifier]->getIdentifier();
        }

        return (SiteService::getCurrent() ?? array_shift($sites))
            ->getIdentifier();
    }

    protected function generateSiteSelectorMenu(): void
    {
        if (!$this->view instanceof BackendTemplateView) {
            throw new RuntimeException(
                sprintf(
                    'Menu for backend docheader can be generated only for view of type `%s`',
                    BackendTemplateView::class
                ),
                1603732307610
            );
        }

        $siteSelectorMenu = $this->view->getModuleTemplate()
            ->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $siteSelectorMenu->setIdentifier(self::SITE_SELECTOR_MENU_KEY);

        foreach ($this->getSites() as $site) {
            $siteSelectorMenu->addMenuItem(
                $this->enrichSiteSelectorMenuItem(
                $siteSelectorMenu->makeMenuItem(),
                $site
                )
            );
        }

        $this->view->getModuleTemplate()->getDocHeaderComponent()
            ->getMenuRegistry()->addMenu($siteSelectorMenu);
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
            $this->uriBuilder->reset()->uriFor(
                'documentation',
                ['siteIdentifier' => $site->getIdentifier()]
            )
        );
        $menuItem->setDataAttributes(['siteIdentifier' => $site->getIdentifier()]);

        return $menuItem;
    }

    protected function setSelectedItemInSiteSelectorMenu(Site $site): void
    {
        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()
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
            if (
                $menuItem->getDataAttributes()['siteIdentifier']
                === $site->getIdentifier()
            ) {
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
        $userModuleData = $GLOBALS['BE_USER']->getModuleData('tx_t3api_m1')
            ?? [];
        $userModuleData[$variable] = $value;
        $GLOBALS['BE_USER']->pushModuleData('tx_t3api_m1', $userModuleData);
    }
}
