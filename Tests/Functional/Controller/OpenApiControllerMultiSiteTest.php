<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Controller;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Controller\OpenApiController;
use SourceBroker\T3api\Service\SiteService;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Covers the site selector of the backend module: the OpenAPI specification must be generated
 * for the site passed in the `site` query parameter, not for the site matching the backend URL.
 *
 * `SiteService` memoizes the current site and the site list for the lifetime of the PHP process,
 * so it is reset around every test to resolve the site from scratch.
 */
class OpenApiControllerMultiSiteTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/t3api',
        'typo3conf/ext/t3api/Tests/Functional/Fixtures/Extensions/t3api_functional_test',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->writeSiteConfiguration('main', 1, 'https://main.example.com/', '_api');
        $this->writeSiteConfiguration('second', 2, 'https://second.example.com/', 'v1');
        $this->flushSiteConfigurationCaches();
        SiteService::reset();
    }

    protected function tearDown(): void
    {
        SiteService::reset();
        parent::tearDown();
    }

    #[Test]
    public function specificationOfMainSiteUsesItsBaseUrlAndApiBasePath(): void
    {
        self::assertSame('https://main.example.com/_api', $this->getSpecificationForSite('main')['servers'][0]['url']);
    }

    #[Test]
    public function specificationOfSecondSiteUsesItsBaseUrlAndApiBasePath(): void
    {
        self::assertSame('https://second.example.com/v1', $this->getSpecificationForSite('second')['servers'][0]['url']);
    }

    private function getSpecificationForSite(string $siteIdentifier): array
    {
        // The backend request is served on the main site's domain, so the selected site can only
        // come from the `site` query parameter.
        $request = (new ServerRequest('https://main.example.com/typo3/module/t3api/open_api_resources', 'GET'))
            ->withQueryParams(['site' => $siteIdentifier])
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $response = $this->get(OpenApiController::class)->resourcesAction($request);

        self::assertSame(200, $response->getStatusCode());

        return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Writes the site configuration file directly, see AbstractDispatcherTestCase::writeSiteConfiguration().
     */
    private function writeSiteConfiguration(string $identifier, int $rootPageId, string $base, string $apiBasePath): void
    {
        $siteConfigurationDirectory = Environment::getConfigPath() . '/sites/' . $identifier;
        GeneralUtility::mkdir_deep($siteConfigurationDirectory);
        file_put_contents($siteConfigurationDirectory . '/config.yaml', Yaml::dump([
            'rootPageId' => $rootPageId,
            'base' => $base,
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
                    'basePath' => $apiBasePath,
                ],
            ],
        ], 99, 2));
    }

    /**
     * See AbstractDispatcherTestCase::flushSiteConfigurationCaches().
     */
    private function flushSiteConfigurationCaches(): void
    {
        $cacheManager = $this->get(CacheManager::class);
        $cacheManager->getCache('core')->remove('sites-configuration');
        $cacheManager->getCache('runtime')->remove('sites-configuration');
    }
}
