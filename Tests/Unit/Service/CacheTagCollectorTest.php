<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Service\CacheTagCollector;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CacheTagCollectorTest extends UnitTestCase
{
    #[Test]
    public function collectsUniqueTagsBetweenStartAndStop(): void
    {
        $collector = new CacheTagCollector();
        $collector->start();
        $collector->addTags('tx_foo', 'tx_foo_1');
        $collector->addTags('tx_foo', 'tx_foo_2');

        self::assertSame(['tx_foo', 'tx_foo_1', 'tx_foo_2'], $collector->stop());
    }

    #[Test]
    public function ignoresTagsWhenNotCollecting(): void
    {
        $collector = new CacheTagCollector();
        $collector->addTags('tx_foo');

        self::assertFalse($collector->isCollecting());
        self::assertSame([], $collector->stop());
    }

    #[Test]
    public function startResetsPreviouslyCollectedTags(): void
    {
        $collector = new CacheTagCollector();
        $collector->start();
        $collector->addTags('tx_old');
        $collector->start();
        $collector->addTags('tx_new');

        self::assertSame(['tx_new'], $collector->stop());
    }

    #[Test]
    public function stopEndsCollecting(): void
    {
        $collector = new CacheTagCollector();
        $collector->start();
        $collector->stop();

        self::assertFalse($collector->isCollecting());
    }
}
