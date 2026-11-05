<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Functional\Dispatcher;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Tests\Functional\Fixtures\ReadCountingCacheBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

class ApiResourceCacheDispatcherTest extends AbstractDispatcherTestCase
{
    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'caching' => [
                'cacheConfigurations' => [
                    't3api' => [
                        'frontend' => VariableFrontend::class,
                        'backend' => ReadCountingCacheBackend::class,
                        'groups' => ['system'],
                    ],
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/products.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/categories.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tags.csv');
    }

    #[Test]
    public function collectionWithRelatedResourcesReadsApiResourcesFromCacheOnce(): void
    {
        $this->dispatchGet('https://example.com/_api/products');

        self::assertSame(1, $this->getT3apiCacheBackend()->readCount);
    }

    private function getT3apiCacheBackend(): ReadCountingCacheBackend
    {
        $backend = $this->get(CacheManager::class)->getCache('t3api')->getBackend();
        self::assertInstanceOf(ReadCountingCacheBackend::class, $backend);

        return $backend;
    }
}
