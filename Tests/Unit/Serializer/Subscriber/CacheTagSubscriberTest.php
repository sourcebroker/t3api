<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Serializer\Subscriber;

use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use PHPUnit\Framework\Attributes\Test;
use SourceBroker\T3api\Serializer\Subscriber\CacheTagSubscriber;
use SourceBroker\T3api\Service\CacheTagCollector;
use SourceBroker\T3api\Tests\Unit\Fixtures\Domain\Model\PlainBook;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CacheTagSubscriberTest extends UnitTestCase
{
    #[Test]
    public function addsTableAndRecordTagsForDomainObjects(): void
    {
        $book = new PlainBook();
        $book->_setProperty('uid', 5);

        $dataMap = new DataMap(PlainBook::class, 'tx_test_domain_model_plainbook');
        $dataMapper = $this->createMock(DataMapper::class);
        $dataMapper->method('getDataMap')->with(PlainBook::class)->willReturn($dataMap);

        $collector = new CacheTagCollector();
        $collector->start();

        $subscriber = new CacheTagSubscriber($collector, $dataMapper);
        $subscriber->onPostSerialize($this->createObjectEvent($book));

        self::assertSame(
            ['tx_test_domain_model_plainbook', 'tx_test_domain_model_plainbook_5'],
            $collector->stop()
        );
    }

    #[Test]
    public function addsOnlyTableTagForDomainObjectsWithoutUid(): void
    {
        $dataMap = new DataMap(PlainBook::class, 'tx_test_domain_model_plainbook');
        $dataMapper = $this->createMock(DataMapper::class);
        $dataMapper->method('getDataMap')->with(PlainBook::class)->willReturn($dataMap);

        $collector = new CacheTagCollector();
        $collector->start();

        $subscriber = new CacheTagSubscriber($collector, $dataMapper);
        $subscriber->onPostSerialize($this->createObjectEvent(new PlainBook()));

        self::assertSame(['tx_test_domain_model_plainbook'], $collector->stop());
    }

    #[Test]
    public function doesNothingWhenCollectorIsInactive(): void
    {
        $dataMapper = $this->createMock(DataMapper::class);
        $dataMapper->expects(self::never())->method('getDataMap');

        $book = new PlainBook();
        $book->_setProperty('uid', 5);

        $subscriber = new CacheTagSubscriber(new CacheTagCollector(), $dataMapper);
        $subscriber->onPostSerialize($this->createObjectEvent($book));
    }

    #[Test]
    public function doesNothingForNonDomainObjects(): void
    {
        $dataMapper = $this->createMock(DataMapper::class);
        $dataMapper->expects(self::never())->method('getDataMap');

        $collector = new CacheTagCollector();
        $collector->start();

        $subscriber = new CacheTagSubscriber($collector, $dataMapper);
        $subscriber->onPostSerialize($this->createObjectEvent(new \stdClass()));

        self::assertSame([], $collector->stop());
    }

    private function createObjectEvent(object $object): ObjectEvent
    {
        return new ObjectEvent(
            $this->createMock(Context::class),
            $object,
            ['name' => get_class($object), 'params' => []]
        );
    }
}
