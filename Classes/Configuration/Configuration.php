<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Configuration;

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
}
