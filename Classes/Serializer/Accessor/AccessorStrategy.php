<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Accessor;

use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\LogicException;
use JMS\Serializer\Expression\ExpressionEvaluator;
use JMS\Serializer\Metadata\ExpressionPropertyMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use SourceBroker\T3api\Service\SerializerService;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AccessorStrategy
 */
class AccessorStrategy implements AccessorStrategyInterface
{
    /**
     * @var ExpressionEvaluator
     */
    protected $evaluator;

    public function __construct()
    {
        $this->evaluator = SerializerService::getExpressionEvaluator();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(object $object, PropertyMetadata $metadata, SerializationContext $context)
    {
        try {
            if ($metadata instanceof ExpressionPropertyMetadata) {
                $variables = ['object' => $object, 'context' => $context, 'property_metadata' => $metadata];
                return $this->evaluator->evaluate((string)($metadata->expression), $variables);
            }

            if ($metadata->getter === null) {
                return ObjectAccess::getProperty($object, $metadata->name);
            }
            return $object->{$metadata->getter}();
        } catch (\Exception $exception) {
            $exclusionForExceptions = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['serializer']['exclusionForExceptionsInAccessorStrategyGetValue'];
            foreach ($exclusionForExceptions as $objectClass => $exceptionClasses) {
                if ($object instanceof $objectClass) {
                    if (in_array('*', $exceptionClasses, true)) {
                        trigger_error($exception->getMessage(), E_USER_WARNING);
                        return null;
                    }
                    foreach ($exceptionClasses as $exceptionClass) {
                        if ($exception instanceof $exceptionClass) {
                            trigger_error($exception->getMessage(), E_USER_WARNING);
                            return null;
                        }
                    }
                }
            }
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(object $object, $value, PropertyMetadata $metadata, DeserializationContext $context): void
    {
        if ($metadata->readOnly === true) {
            throw new LogicException(sprintf('Property `%s` on `%s` is read only.', $metadata->name, $metadata->class));
        }

        if ($metadata->setter === null) {
            ObjectAccess::setProperty($object, $metadata->name, $value);

            return;
        }

        $object->{$metadata->setter}($value);
    }
}
