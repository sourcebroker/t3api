<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use SourceBroker\T3api\Service\SerializerService;
use Throwable;

class ThrowableSubscriber implements EventSubscriberInterface
{
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
        if (!$event->getObject() instanceof Throwable) {
            return;
        }

        /** @var Throwable $object */
        $object = $event->getObject();

        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        $this->addDescription($object, $visitor);
        $this->addDebug($object, $visitor);
    }

    protected function addDescription(Throwable $throwable, JsonSerializationVisitor $visitor): void
    {
        $visitor->visitProperty(
            new StaticPropertyMetadata(get_class($throwable), 'hydra:description', $throwable->getMessage()),
            $throwable->getMessage()
        );
    }

    protected function addDebug(Throwable $throwable, JsonSerializationVisitor $visitor): void
    {
        if (!SerializerService::isDebugMode()) {
            return;
        }

        $debug = [];

        foreach ($throwable->getTrace() as $trace) {
            $debug[] = [
                'file' => $trace['file'] ?? '',
                'line' => $trace['line'] ?? '',
                'function' => $trace['function'] ?? '',
                'class' => $trace['class'] ?? '',
            ];
        }

        $visitor->visitProperty(
            new StaticPropertyMetadata(get_class($throwable), 'hydra:debug', $debug),
            $debug
        );
    }

}
