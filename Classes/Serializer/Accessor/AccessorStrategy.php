<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Accessor;

use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\LogicException;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use Throwable;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AccessorStrategy
 */
class AccessorStrategy implements AccessorStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue(object $object, PropertyMetadata $metadata, SerializationContext $context)
    {
        if (null === $metadata->getter) {
            try {
                return ObjectAccess::getProperty(
                    $object,
                    $metadata->name,
                    false && !ObjectAccess::isPropertyGettable($object, $metadata->name)
                );
            } catch (Throwable $throwable) {
                throw new \RuntimeException(
                    sprintf(
                        'Could not read property `%s` of `%s`. Use serializer `Exclude()` annotation or, if you are not allowed to overwrite class file, provide proper YAML configuration to exclude this property (search for `serializerMetadataDirs` to see how to provide it).',
                        $metadata->name,
                        $metadata->class
                    ),
                    1565871708259,
                    $throwable
                );
            }
        }

        return $object->{$metadata->getter}();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(object $object, $value, PropertyMetadata $metadata, DeserializationContext $context): void
    {
        if (true === $metadata->readOnly) {
            throw new LogicException(sprintf('%s on %s is read only.', $metadata->name, $metadata->class));
        }

        if (null === $metadata->setter) {
            ObjectAccess::setProperty($object, $metadata->name, $value);

            return;
        }

        $object->{$metadata->setter}($value);
    }
}
