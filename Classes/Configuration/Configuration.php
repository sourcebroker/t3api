<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Configuration;

use SourceBroker\T3api\Provider\ApiResourcePath\ApiResourcePathProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{
    public static function getOperationHandlers(): array
    {
        trigger_error('Configuration::getOperationHandlers() will be removed in t3api v4.0.', E_USER_DEPRECATED);
        return self::getClassNamesSortedByPriority($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['operationHandlers'] ?? []);
    }

    public static function getProcessors(): array
    {
        trigger_error('Configuration::getProcessors() will be removed in t3api v4.0.', E_USER_DEPRECATED);
        return self::getClassNamesSortedByPriority($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['processors'] ?? []);
    }

    public static function getSerializerHandlers(): array
    {
        trigger_error('Configuration::getSerializerHandlers() will be removed in t3api v4.0.', E_USER_DEPRECATED);
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerHandlers'] ?? [];
    }

    public static function getSerializerSubscribers(): array
    {
        trigger_error('Configuration::getSerializerSubscribers() will be removed in t3api v4.0.', E_USER_DEPRECATED);
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerSubscribers'] ?? [];
    }

    public static function getSerializerObjectConstructors(): array
    {
        trigger_error('Configuration::getSerializerObjectConstructors() will be removed in t3api v4.0.', E_USER_DEPRECATED);
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializerObjectConstructors'] ?? [];
    }

    public static function getCollectionResponseClass(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'];
    }

    public static function getCors(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['cors'];
    }

    /**
     * @return \Generator|ApiResourcePathProvider[]
     */
    public static function getApiResourcePathProviders(): \Generator
    {
        trigger_error('Configuration::getApiResourcePathProviders() will be removed in t3api v4.0.', E_USER_DEPRECATED);

        $apiResourcePathProvidersClasses
            = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['apiResourcePathProviders'] ?? [];

        foreach ($apiResourcePathProvidersClasses as $apiResourcePathProviderClass) {
            $apiResourcePathProvider = GeneralUtility::makeInstance($apiResourcePathProviderClass);

            if (!$apiResourcePathProvider instanceof ApiResourcePathProvider) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'API resource path provider `%s` has to be an instance of `%s`',
                        $apiResourcePathProviderClass,
                        ApiResourcePathProvider::class
                    ),
                    1609066405400
                );
            }

            yield $apiResourcePathProvider;
        }
    }

    protected static function getClassNamesSortedByPriority(?array $items): array
    {
        $items = $items ?? [];
        $items = array_map(
            static function ($class, $priority): array {
                return [
                    'className' => $class,
                    'priority' => is_numeric($priority) ? $priority : 50,
                ];
            },
            array_keys($items),
            $items
        );

        usort(
            $items,
            static function (array $itemA, array $itemB): int {
                return $itemB['priority'] <=> $itemA['priority'];
            }
        );

        return array_column($items, 'className');
    }
}
