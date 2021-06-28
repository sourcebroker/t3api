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
        if ($metadata instanceof ExpressionPropertyMetadata) {
            $variables = ['object' => $object, 'context' => $context, 'property_metadata' => $metadata];

            return $this->evaluator->evaluate((string)($metadata->expression), $variables);
        }

        if (null === $metadata->getter) {
            return ObjectAccess::getProperty($object, $metadata->name, false);
        }

        return $object->{$metadata->getter}();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(object $object, $value, PropertyMetadata $metadata, DeserializationContext $context): void
    {
        if (true === $metadata->readOnly) {
            throw new LogicException(sprintf('Property `%s` on `%s` is read only.', $metadata->name, $metadata->class));
        }

        if (null === $metadata->setter) {
            ObjectAccess::setProperty($object, $metadata->name, $value);

            return;
        }

        $object->{$metadata->setter}($value);
    }
}
