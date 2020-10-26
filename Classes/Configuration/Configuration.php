<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Configuration;

class Configuration
{
    public static function getOperationHandlers(): array
    {
        $operationHandlers = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['operationHandlers'];
        return self::getClassNames($operationHandlers);
    }

    public static function getProcessors(): array
    {
        $processors = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['processors'];
        return self::getClassNames($processors);
    }

    public static function getCollectionResponseClass(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['collectionResponseClass'];
    }

    protected static function getClassNames(?array $items): array
    {
        $items = $items ?: [];
        $items = array_map(
            static function ($class, $priority) {
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
            static function (array $itemA, array $itemB) {
                return $itemB['priority'] <=> $itemA['priority'];
            }
        );

        return array_column($items, 'className');
    }
}
