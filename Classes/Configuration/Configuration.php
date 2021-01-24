<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Configuration;

use Generator;
use InvalidArgumentException;
use SourceBroker\T3api\Provider\ApiResourcePath\ApiResourcePathProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{
    public static function getOperationHandlers(): array
    {
        $operationHandlers = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['operationHandlers'];

        $operationHandlers = array_map(
            static function ($class, $priority) {
                return [
                    'className' => $class,
                    'priority' => is_numeric($priority) ? $priority : 50,
                ];
            },
            array_keys($operationHandlers),
            $operationHandlers
        );

        usort(
            $operationHandlers,
            static function (array $operationHandlerA, array $operationHandlerB) {
                return $operationHandlerB['priority'] <=> $operationHandlerA['priority'];
            }
        );

        return array_column($operationHandlers, 'className');
    }

    public static function getCollectionResponseClass(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'];
    }

    /**
     * @return Generator|ApiResourcePathProvider[]
     */
    public static function getApiResourcePathProviders(): Generator
    {
        $apiResourcePathProvidersClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['apiResourcePathProviders'];

        foreach ($apiResourcePathProvidersClasses as $apiResourcePathProviderClass) {
            $apiResourcePathProvider = GeneralUtility::makeInstance($apiResourcePathProviderClass);

            if (!$apiResourcePathProvider instanceof ApiResourcePathProvider) {
                throw new InvalidArgumentException(
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
