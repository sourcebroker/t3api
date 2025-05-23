<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Builder\DefaultDriverFactory;
use JMS\Serializer\Builder\DriverFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\Expression\ExpressionEvaluator;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Type\Parser;
use Metadata\Cache\FileCache;
use Metadata\MetadataFactory;
use Metadata\MetadataFactoryInterface;
use SourceBroker\T3api\Serializer\Accessor\AccessorStrategy;
use SourceBroker\T3api\Serializer\Construction\ObjectConstructorChain;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Serializer\ContextBuilder\SerializationContextBuilder;
use SourceBroker\T3api\Utility\FileUtility;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SerializerService implements SingletonInterface
{
    public function __construct(
        protected readonly SerializationContextBuilder $serializationContextBuilder,
        protected readonly DeserializationContextBuilder $deserializationContextBuilder
    ) {}

    public static function getSerializerCacheDirectory(): string
    {
        return FileUtility::createWritableDirectory(Environment::getVarPath() . '/cache/code/t3api/jms-serializer');
    }

    public static function getAnnotationsCacheDirectory(): string
    {
        return FileUtility::createWritableDirectory(self::getSerializerCacheDirectory() . '/annotations');
    }

    public static function getAutogeneratedMetadataDirectory(): string
    {
        return FileUtility::createWritableDirectory(Environment::getVarPath() . '/cache/code/t3api/jms-metadir');
    }

    public static function getMetadataCache(): FileCache
    {
        return new FileCache(FileUtility::createWritableDirectory(self::getSerializerCacheDirectory() . '/metadata'));
    }

    public static function clearCache(array $params): void
    {
        if (isset($params['cacheCmd']) && in_array($params['cacheCmd'], ['all', 'system'], true)) {
            $filesystemService = GeneralUtility::makeInstance(FilesystemService::class);
            $filesystemService->flushDirectory(self::getSerializerCacheDirectory(), true, true);
            $filesystemService->flushDirectory(self::getAutogeneratedMetadataDirectory(), true, true);
        }
    }

    public static function isDebugMode(): bool
    {
        return Environment::getContext()->isDevelopment();
    }

    public function serialize(mixed $result, ?SerializationContext $serializationContext = null): string
    {
        return $this->getSerializerBuilder()
            ->setSerializationContextFactory(function () use ($serializationContext): SerializationContext {
                return $serializationContext ?? $this->serializationContextBuilder->create();
            })
            ->build()
            ->serialize($result, 'json');
    }

    public function deserialize(string $data, string $type, ?DeserializationContext $deserializationContext = null): mixed
    {
        return $this->getSerializerBuilder()
            ->setDeserializationContextFactory(function () use ($deserializationContext): DeserializationContext {
                return $deserializationContext ?? $this->deserializationContextBuilder->create();
            })
            ->build()
            ->deserialize($data, $type, 'json');
    }

    public function getSerializerBuilder(): SerializerBuilder
    {
        static $serializerBuilder;

        if (empty($serializerBuilder)) {
            $serializerBuilder = SerializerBuilder::create()
                ->setCacheDir(self::getSerializerCacheDirectory())
                ->setDebug(self::isDebugMode())
                ->configureHandlers(static function (HandlerRegistry $registry): void {
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerHandlers'] ?? [] as $handlerClass) {
                        /** @var SubscribingHandlerInterface $handler */
                        $handler = GeneralUtility::makeInstance($handlerClass);
                        $registry->registerSubscribingHandler($handler);
                    }
                })
                ->configureListeners(static function (EventDispatcher $dispatcher): void {
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'] ?? [] as $subscriberClass) {
                        /** @var EventSubscriberInterface $subscriber */
                        $subscriber = GeneralUtility::makeInstance($subscriberClass);
                        $dispatcher->addSubscriber($subscriber);
                    }
                })
                ->addDefaultHandlers()
                ->setAccessorStrategy(GeneralUtility::makeInstance(AccessorStrategy::class))
                ->setPropertyNamingStrategy($this->getPropertyNamingStrategy())
                ->setAnnotationReader(self::getAnnotationReader())
                ->setMetadataDriverFactory($this->getDriverFactory())
                ->setMetadataCache(self::getMetadataCache())
                ->addMetadataDirs(self::getMetadataDirs())
                ->setObjectConstructor(GeneralUtility::makeInstance(
                    ObjectConstructorChain::class,
                    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerObjectConstructors']
                ))
                ->setExpressionEvaluator(self::getExpressionEvaluator());
        }

        return clone $serializerBuilder;
    }

    public function getMetadataFactory(): MetadataFactoryInterface
    {
        $metadataDriver = $this->getDriverFactory()
            ->createDriver(self::getMetadataDirs(), self::getAnnotationReader());
        $metadataFactory = new MetadataFactory($metadataDriver, null, self::isDebugMode());
        $metadataFactory->setCache(self::getMetadataCache());

        return $metadataFactory;
    }

    public static function getExpressionEvaluator(): ExpressionEvaluator
    {
        return new ExpressionEvaluator(ExpressionLanguageService::getT3apiExpressionLanguage());
    }

    protected static function getMetadataDirs(): array
    {
        return ['' => self::getAutogeneratedMetadataDirectory()];
    }

    /**
     * @throws \RuntimeException
     */
    protected static function getAnnotationReader(): Reader
    {
        return new PsrCachedReader(
            new AnnotationReader(),
            new FilesystemAdapter('', 0, self::getAnnotationsCacheDirectory()),
            self::isDebugMode()
        );
    }

    /**
     * @return SerializedNameAnnotationStrategy
     */
    protected function getPropertyNamingStrategy(): PropertyNamingStrategyInterface
    {
        return GeneralUtility::makeInstance(
            SerializedNameAnnotationStrategy::class,
            GeneralUtility::makeInstance(IdenticalPropertyNamingStrategy::class)
        );
    }

    protected function getDriverFactory(): DriverFactoryInterface
    {
        return new DefaultDriverFactory(
            $this->getPropertyNamingStrategy(),
            new Parser(),
            self::getExpressionEvaluator()
        );
    }
}
