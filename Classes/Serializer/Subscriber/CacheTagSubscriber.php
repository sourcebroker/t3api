<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use SourceBroker\T3api\Service\CacheTagCollector;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class CacheTagSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly CacheTagCollector $cacheTagCollector,
        protected readonly DataMapper $dataMapper
    ) {}

    /**
     * @return array<int, array<string, string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        if (!$this->cacheTagCollector->isCollecting()) {
            return;
        }

        $object = $event->getObject();
        if (!$object instanceof AbstractDomainObject) {
            return;
        }

        $tableName = $this->dataMapper->getDataMap($event->getType()['name'])->getTableName();
        if ($object->getUid() === null) {
            $this->cacheTagCollector->addTags($tableName);

            return;
        }

        $this->cacheTagCollector->addTags($tableName, $tableName . '_' . $object->getUid());
    }
}
