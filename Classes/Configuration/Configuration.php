<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Configuration;

class Configuration
{
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

    protected static function getClassNamesSortedByPriority(?array $items): array
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
