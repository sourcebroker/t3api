<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Domain\Repository;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Factory\ApiResourceFactory;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use SourceBroker\T3api\Service\ReflectionService;
use SourceBroker\T3api\Service\SiteService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ApiResourceRepositoryTest extends UnitTestCase
{
    /**
     * @var string[]
     */
    private array $readCacheIdentifiers = [];

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();
        SiteService::reset();
        $this->site = new Site('test', 1, [
            'base' => 'https://example.com/',
            'languages' => [
                ['languageId' => 0, 'title' => 'English', 'base' => '/', 'locale' => 'en_US.UTF-8'],
                ['languageId' => 1, 'title' => 'German', 'base' => '/de/', 'locale' => 'de_DE.UTF-8'],
            ],
            'routeEnhancers' => [
                'T3api' => ['type' => ResourceEnhancer::ENHANCER_NAME, 'basePath' => '_api'],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        SiteService::reset();
        parent::tearDown();
    }

    #[Test]
    public function getByEntityReadsCacheOnlyOnceForManyEntities(): void
    {
        $this->setCurrentRequestLanguage(0);
        $apiResourceRepository = $this->createApiResourceRepository();

        $apiResourceRepository->getByEntity(\stdClass::class);
        $apiResourceRepository->getByEntity(\stdClass::class);
        $apiResourceRepository->getByEntity(\stdClass::class);

        self::assertSame(['ApiResourceRepository_getAll_api'], $this->readCacheIdentifiers);
    }

    #[Test]
    public function getAllReadsCacheOncePerApiBasePath(): void
    {
        $apiResourceRepository = $this->createApiResourceRepository();

        foreach ([0, 1, 0, 1] as $languageId) {
            $this->setCurrentRequestLanguage($languageId);
            $apiResourceRepository->getAll();
        }

        self::assertSame(
            ['ApiResourceRepository_getAll_api', 'ApiResourceRepository_getAllde_api'],
            $this->readCacheIdentifiers
        );
    }

    private function createApiResourceRepository(): ApiResourceRepository
    {
        $cache = self::createStub(FrontendInterface::class);
        $cache->method('get')->willReturnCallback(function (string $cacheIdentifier): array {
            $this->readCacheIdentifiers[] = $cacheIdentifier;

            return [];
        });
        $cacheManager = self::createStub(CacheManager::class);
        $cacheManager->method('getCache')->willReturnMap([['t3api', $cache]]);

        return new ApiResourceRepository(
            $cacheManager,
            self::createStub(ReflectionService::class),
            self::createStub(ApiResourceFactory::class)
        );
    }

    private function setCurrentRequestLanguage(int $languageId): void
    {
        $language = $this->site->getLanguageById($languageId);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest($language->getBase() . '_api/news/news', 'GET'))
            ->withAttribute('site', $this->site)
            ->withAttribute('language', $language);
    }
}
