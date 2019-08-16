<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use SourceBroker\T3api\Domain\Model\AbstractOperation;
use SourceBroker\T3api\Serializer\Accessor\AccessorStrategy;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class SerializerService
 */
class SerializerService implements SingletonInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param mixed $result
     *
     * @return string
     */
    public function serialize($result)
    {
        return $this->getSerializer()->serialize($result, 'json');
    }

    /**
     * @param AbstractOperation $operation
     * @param mixed $result
     *
     * @return string
     */
    public function serializeOperation(AbstractOperation $operation, $result)
    {
        return $this->getSerializerForOperation($operation)->serialize($result, 'json');
    }

    /**
     * @param AbstractOperation $operation
     *
     * @return SerializerInterface
     */
    protected function getSerializerForOperation(AbstractOperation $operation): SerializerInterface
    {
        return $this->getSerializerBuilder()
            ->setSerializationContextFactory(function () use ($operation) {
                $serializationContext = SerializationContext::create()
                    ->setSerializeNull(true);

                if (!empty($operation->getContextGroups())) {
                    $serializationContext->setGroups($operation->getContextGroups());
                }

                return $serializationContext;
            })
            ->build();
    }

    /**
     * @return SerializerInterface
     */
    protected function getSerializer(): SerializerInterface
    {
        return $this->getSerializerBuilder()->build();
    }

    /**
     * @return SerializerBuilder
     */
    protected function getSerializerBuilder(): SerializerBuilder
    {
        static $serializerBuilder;

        if (!empty($serializerBuilder)) {
            return $serializerBuilder;
        }

        $serializerBuilder = SerializerBuilder::create()
            ->setCacheDir($this->getSerializerCacheDirectory())
            ->setDebug(GeneralUtility::getApplicationContext()->isDevelopment())
            ->configureHandlers(function (HandlerRegistry $registry) {
                foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerHandlers'] ?? [] as $handlerClass) {
                    /** @var SubscribingHandlerInterface $handler */
                    $handler = $this->objectManager->get($handlerClass);
                    $registry->registerSubscribingHandler($handler);
                }
            })
            ->configureListeners(function (EventDispatcher $dispatcher) {
                foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'] ?? [] as $subscriberClass) {
                    /** @var EventSubscriberInterface $subscriber */
                    $subscriber = $this->objectManager->get($subscriberClass);
                    $dispatcher->addSubscriber($subscriber);
                }
            })
            ->addDefaultHandlers()
            ->setAccessorStrategy($this->objectManager->get(AccessorStrategy::class))
            ->setPropertyNamingStrategy(
                $this->objectManager->get(
                    SerializedNameAnnotationStrategy::class,
                    $this->objectManager->get(IdenticalPropertyNamingStrategy::class)
                )
            )
            ->addMetadataDirs($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerMetadataDirs'] ?? []);

        // @todo add signal for serializer customization

        return $serializerBuilder;
    }

    /**
     * @return string
     */
    protected function getSerializerCacheDirectory(): string
    {
        $cacheDirectory = PATH_site . '/typo3temp/var/cache/code/t3api/jms-serializer';
        if (!file_exists($cacheDirectory)) {
            mkdir($cacheDirectory, 0777, true);
        }
        GeneralUtility::fixPermissions($cacheDirectory);

        return $cacheDirectory;
    }
}
