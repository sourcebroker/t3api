<?php

declare(strict_types=1);

namespace SourceBroker\T3api\EventListener;

use SourceBroker\T3api\Service\SiteService;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Event\PolicyMutatedEvent;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Swagger UI in the t3api backend module sends "Try it out" requests to the API of the selected
 * site. When that site runs on another domain than the backend, the backend's Content Security
 * Policy (`default-src 'self'`) blocks the request - so the origins of all sites with the t3api
 * route enhancer are allowed as `connect-src` for the t3api backend module.
 */
class AllowApiSitesInBackendContentSecurityPolicyEventListener
{
    public function __construct(
        protected readonly SiteFinder $siteFinder,
    ) {}

    public function __invoke(PolicyMutatedEvent $event): void
    {
        if ($event->scope !== Scope::backend() || !$this->isT3apiModuleRequest($event)) {
            return;
        }

        $origins = $this->getApiSiteOrigins();
        if ($origins === []) {
            return;
        }

        $event->setCurrentPolicy(
            $event->getCurrentPolicy()->extend(Directive::ConnectSrc, ...array_values($origins))
        );
    }

    protected function isT3apiModuleRequest(PolicyMutatedEvent $event): bool
    {
        return str_contains($event->request?->getUri()->getPath() ?? '', '/module/t3api');
    }

    /**
     * @return array<string, UriValue> origins (scheme, host and port) of all site and site language
     *                                 bases with a host, keyed by their string representation
     */
    protected function getApiSiteOrigins(): array
    {
        $origins = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            if (!SiteService::hasT3apiRouteEnhancer($site)) {
                continue;
            }

            $bases = [$site->getBase()];
            foreach ($site->getAllLanguages() as $language) {
                $bases[] = $language->getBase();
            }

            foreach ($bases as $base) {
                if ($base->getHost() === '') {
                    continue;
                }
                $origin = UriValue::fromUri($base->withUserInfo('')->withPath('')->withQuery('')->withFragment(''));
                $origins[(string)$origin] = $origin;
            }
        }

        return $origins;
    }
}
