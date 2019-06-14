<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use SourceBroker\Restify\Transformer\TransformerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class TransformerHandler
 *
 * @package SourceBroker\Restify\Serializer\Handler
 */
class TransformerHandler implements SubscribingHandlerInterface
{
    /**
     * @var object|ObjectManager
     */
    protected $objectManager;

    /**
     * TransformerHandler constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array_map(function ($type) {
            return [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => $type,
                'method' => 'serialize',
            ];
        }, array_column($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['transformers'] ?? [], 'type'));
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param mixed $value
     * @param array $type
     * @param Context $context
     */
    public function serialize(JsonSerializationVisitor $visitor, $value, array $type, Context $context)
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restify']['transformers'] as $transformerConfig) {
            if ($transformerConfig['type'] === get_class($value)) {
                /** @var TransformerInterface $transformer */
                $transformer = $this->objectManager->get($transformerConfig['class']);

                if (!$transformer instanceof TransformerInterface) {
                    throw new \InvalidArgumentException(
                        sprintf('%s is not an instance of TransformerInterface', get_class($transformer)),
                        1560409736743
                    );
                }

                $params = [$value];

                return call_user_func_array([$transformer, 'serialize'], $params);
            }
        }
    }
}
