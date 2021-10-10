<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\ContextBuilder;

use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher as SignalSlotDispatcher;

abstract class AbstractContextBuilder implements ContextBuilderInterface
{
    protected static function getCustomizedContextAttributes(
        OperationInterface $operation,
        Request $request,
        array $attributes
    ): array {
        $signalOutput = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(SignalSlotDispatcher::class)
            ->dispatch(
                ContextBuilderInterface::class,
                ContextBuilderInterface::SIGNAL_CUSTOMIZE_SERIALIZER_CONTEXT_ATTRIBUTES,
                [
                    clone $operation,
                    clone $request,
                    $attributes,
                ]
            );
        if (!is_array($signalOutput[2])) {
            throw new \RuntimeException(
                sprintf(
                    'Serializer context `attributes` returned from `%s` has to be an type of array %s returned',
                    ContextBuilderInterface::SIGNAL_CUSTOMIZE_SERIALIZER_CONTEXT_ATTRIBUTES,
                    gettype($signalOutput[2])
                ),
                1587379831963
            );
        }

        return $signalOutput[2];
    }
}
