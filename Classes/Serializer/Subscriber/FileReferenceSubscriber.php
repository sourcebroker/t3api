<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Event;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Serializer\Handler\FileReferenceHandler;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;

/**
 * Class FileReferenceSubscriber
 */
class FileReferenceSubscriber implements EventSubscriberInterface
{
    /**
     * @var ApiResourceRepository
     */
    protected $apiResourceRepository;

    /**
     * @param ApiResourceRepository $apiResourceRepository
     */
    public function __construct(ApiResourceRepository $apiResourceRepository)
    {
        $this->apiResourceRepository = $apiResourceRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'method' => 'onPreSerialize',
            ],
            [
                'event' => Events::PRE_DESERIALIZE,
                'method' => 'onPreDeserialize',
            ],
        ];
    }

    /**
     * Changes type to the custom one to make it possible to handle data with serializer handler
     *
     * @param PreSerializeEvent $event
     */
    public function onPreSerialize(PreSerializeEvent $event): void
    {
        $this->changeTypeToHandleAllFileReferenceExtendingClasses($event);
    }

    /**
     * Changes type to the custom one to make it possible to handle data with serializer handler
     *
     * @param PreDeserializeEvent $event
     */
    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        $this->changeTypeToHandleAllFileReferenceExtendingClasses($event);
    }

    /**
     * @param Event $event
     */
    protected function changeTypeToHandleAllFileReferenceExtendingClasses(Event $event): void
    {
        if (
            is_subclass_of($event->getType()['name'], AbstractFileFolder::class)
            && $event->getContext()->getDepth() > 1
        ) {
            $event->setType(
                FileReferenceHandler::TYPE,
                [
                    'targetType' => $event->getType()['name'],
                ]
            );
        }
    }
}
