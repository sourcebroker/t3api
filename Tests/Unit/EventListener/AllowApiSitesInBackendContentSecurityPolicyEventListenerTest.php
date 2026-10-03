<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\EventListener;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\EventListener\AllowApiSitesInBackendContentSecurityPolicyEventListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Event\PolicyMutatedEvent;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Policy;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AllowApiSitesInBackendContentSecurityPolicyEventListenerTest extends UnitTestCase
{
    #[Test]
    public function addsOriginsOfApiSitesToConnectSrcOfT3apiBackendModule(): void
    {
        $event = $this->createEvent(Scope::backend(), 'https://backend.example.com/typo3/module/t3api?site=api');

        $this->createListener()($event);

        $policy = $event->getCurrentPolicy();
        self::assertTrue($policy->containsDirective(Directive::ConnectSrc, SourceKeyword::self));
        self::assertTrue($policy->containsDirective(Directive::ConnectSrc, new UriValue('https://api.example.com')));
        self::assertTrue($policy->containsDirective(Directive::ConnectSrc, new UriValue('https://de.api.example.com')));
        self::assertFalse($policy->containsDirective(Directive::ConnectSrc, new UriValue('https://no-api.example.com')));
    }

    #[Test]
    public function keepsPolicyOfOtherBackendModulesUnchanged(): void
    {
        $event = $this->createEvent(Scope::backend(), 'https://backend.example.com/typo3/module/web/layout');
        $policyBefore = $event->getCurrentPolicy();

        $this->createListener()($event);

        self::assertSame($policyBefore, $event->getCurrentPolicy());
    }

    #[Test]
    public function keepsFrontendPolicyUnchanged(): void
    {
        $event = $this->createEvent(Scope::frontend(), 'https://www.example.com/module/t3api');
        $policyBefore = $event->getCurrentPolicy();

        $this->createListener()($event);

        self::assertSame($policyBefore, $event->getCurrentPolicy());
    }

    private function createListener(): AllowApiSitesInBackendContentSecurityPolicyEventListener
    {
        $t3apiRouteEnhancer = ['routeEnhancers' => ['T3api' => ['type' => 'T3apiResourceEnhancer']]];
        $sites = [
            // relative base - served on the backend domain, covered by 'self'
            new Site('main', 1, ['base' => '/'] + $t3apiRouteEnhancer),
            new Site('api', 2, [
                'base' => 'https://api.example.com/',
                'languages' => [
                    ['languageId' => 0, 'base' => '/', 'locale' => 'en_US.UTF-8'],
                    ['languageId' => 1, 'base' => 'https://de.api.example.com/', 'locale' => 'de_DE.UTF-8'],
                ],
            ] + $t3apiRouteEnhancer),
            new Site('no-api', 3, ['base' => 'https://no-api.example.com/']),
        ];

        $siteFinder = self::createStub(SiteFinder::class);
        $siteFinder->method('getAllSites')->willReturn($sites);

        return new AllowApiSitesInBackendContentSecurityPolicyEventListener($siteFinder);
    }

    private function createEvent(Scope $scope, string $uri): PolicyMutatedEvent
    {
        $policy = (new Policy())->default(SourceKeyword::self);

        return new PolicyMutatedEvent($scope, new ServerRequest($uri), $policy, $policy);
    }
}
