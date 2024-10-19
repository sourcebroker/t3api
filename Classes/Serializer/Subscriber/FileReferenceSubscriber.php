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
use TYPO3\CMS\Extbase\Domain\Model\File;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Model\Folder;

class FileReferenceSubscriber implements EventSubscriberInterface
{
    public function __construct(protected readonly ApiResourceRepository $apiResourceRepository) {}

    public static function getSubscribedEvents(): array
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
     */
    public function onPreSerialize(PreSerializeEvent $event): void
    {
        $this->changeTypeToHandleAllFileReferenceExtendingClasses($event);
    }

    /**
     * Changes type to the custom one to make it possible to handle data with serializer handler
     */
    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        $this->changeTypeToHandleAllFileReferenceExtendingClasses($event);
    }

    protected function changeTypeToHandleAllFileReferenceExtendingClasses(Event $event): void
    {
        if (
            (
                is_subclass_of($event->getType()['name'], Folder::class)
                || is_subclass_of($event->getType()['name'], File::class)
                || is_subclass_of($event->getType()['name'], FileReference::class)
            )
            && $event->getContext()->getDepth() > 1
        ) {
            /** @var PreDeserializeEvent|PreSerializeEvent $event */
            $event->setType(
                FileReferenceHandler::TYPE,
                [
                    'targetType' => $event->getType()['name'],
                ]
            );
        }
    }
}
