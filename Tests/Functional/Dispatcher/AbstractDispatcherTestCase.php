<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use SourceBroker\T3api\Dispatcher\Bootstrap;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Shared harness for functional tests that dispatch real API requests through t3api's Bootstrap:
 * loads the fixture extension, writes a site configuration with the t3api route enhancer and
 * fakes enough request state for site resolution to work on CLI.
 *
 * `SiteService::getCurrent()` and `RouteService`'s route-enhancer lookup memoize their result
 * in function-local static variables for the lifetime of the PHP process, so every test class
 * extending this harness has to run against the one site configuration written in setUp().
 */
abstract class AbstractDispatcherTestCase extends FunctionalTestCase
{
    protected const SITE_IDENTIFIER = 'functional-test';

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/t3api',
        'typo3conf/ext/t3api/Tests/Functional/Fixtures/Extensions/t3api_functional_test',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->writeSiteConfiguration();

        // SiteService::getCurrent() falls back to TYPO3\CMS\Core\Http\ServerRequestFactory::fromGlobals(),
        // which reads the real PHP superglobals (not $GLOBALS['TYPO3_REQUEST']) and refuses to build a
        // request URL on CLI unless $_SERVER carries one - fake it so site resolution succeeds.
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/_api/books';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $site = $this->get(SiteFinder::class)->getSiteByIdentifier(self::SITE_IDENTIFIER);

        // Extbase's ConfigurationManager resolves this request through $GLOBALS['TYPO3_REQUEST'] to build
        // the persistence QuerySettings used by CommonRepository. Marking it FE would route through
        // FrontendConfigurationManager, which hard-requires a `frontend.typoscript` request attribute that
        // is normally populated by the frontend middleware stack we deliberately bypass here. Marking it BE
        // routes through BackendConfigurationManager instead, which is built to gracefully compute a
        // (page-less, in our case) TypoScript setup without a real page tree - this is the same mechanism
        // every Extbase backend module relies on. `applicationType` itself is not read anywhere in t3api's
        // own code, so this only affects Extbase's internal TypoScript bootstrapping, not dispatch behaviour.
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/_api/books', 'GET'))
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getDefaultLanguage())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
    }

    protected function dispatchGet(string $url): string
    {
        $symfonyRequest = SymfonyRequest::create($url, 'GET');
        $requestContext = (new RequestContext())->fromRequest($symfonyRequest);
        $response = new Response();

        return $this->get(Bootstrap::class)
            ->processOperationByRequest($requestContext, $symfonyRequest, $response);
    }

    /**
     * Writes the site configuration file directly instead of going through
     * `TYPO3\CMS\Core\Configuration\SiteWriter`, which only exists from TYPO3 13.1 onwards
     * (in earlier versions writing was a responsibility of `SiteConfiguration` itself) - this
     * keeps the test working across the whole TYPO3 12/13/14 support range.
     */
    private function writeSiteConfiguration(): void
    {
        $siteConfigurationDirectory = Environment::getConfigPath() . '/sites/' . self::SITE_IDENTIFIER;
        GeneralUtility::mkdir_deep($siteConfigurationDirectory);
        file_put_contents($siteConfigurationDirectory . '/config.yaml', Yaml::dump([
            'rootPageId' => 1,
            'base' => '/',
            'languages' => [
                0 => [
                    'title' => 'English',
                    'enabled' => true,
                    'languageId' => 0,
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                    'navigationTitle' => 'English',
                ],
            ],
            'routeEnhancers' => [
                'T3apiResourceEnhancer' => [
                    'type' => 'T3apiResourceEnhancer',
                    'basePath' => '_api',
                ],
            ],
        ], 99, 2));

        $this->flushSiteConfigurationCaches();
    }

    /**
     * `SiteConfiguration` caches its resolved sites under the fixed identifier `sites-configuration`
     * in both the `core` and `runtime` caches; a direct file write bypasses the invalidation that
     * `SiteWriter::write()`/`SiteConfiguration::write()` would otherwise trigger via
     * `SiteConfigurationChangedEvent`, so both entries are removed explicitly here.
     */
    private function flushSiteConfigurationCaches(): void
    {
        $cacheManager = $this->get(CacheManager::class);
        $cacheManager->getCache('core')->remove('sites-configuration');
        $cacheManager->getCache('runtime')->remove('sites-configuration');
    }
}
