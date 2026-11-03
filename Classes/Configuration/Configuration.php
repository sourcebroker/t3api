<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Configuration;

use SourceBroker\T3api\Provider\ApiResourcePath\ApiResourcePathProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{
    public const DEFAULT_FILE_TOKEN_LIFETIME = 86400;

    public const MAXIMUM_FILE_TOKEN_LIFETIME = 315360000;

    public static function getOperationHandlers(): array
    {
        return self::getClassNamesSortedByPriority($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['operationHandlers']);
    }

    public static function getProcessors(): array
    {
        return self::getClassNamesSortedByPriority($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['processors']);
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
     * Lifetime of file tokens in seconds (`fileToken.lifetime`) - an invalid value (not a positive number) falls back
     * to the default, a value above 10 years is limited to 10 years.
     */
    public static function getFileTokenLifetime(): int
    {
        $lifetime = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['fileToken']['lifetime'] ?? null;
        if (!is_numeric($lifetime) || (int)$lifetime <= 0) {
            return self::DEFAULT_FILE_TOKEN_LIFETIME;
        }

        return min((int)$lifetime, self::MAXIMUM_FILE_TOKEN_LIFETIME);
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

    /**
     * @return \Generator|ApiResourcePathProvider[]
     */
    public static function getApiResourcePathProviders(): \Generator
    {
        $apiResourcePathProvidersClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['apiResourcePathProviders'];

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
}
