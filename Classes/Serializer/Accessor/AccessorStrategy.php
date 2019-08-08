<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Accessor;

use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\LogicException;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
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
                    $metadata->serializedName
                );
            } catch (\Error $error) {
                // if error was thrown it means getter for protected property does not exist
                // to support self::TYPES_WITH_ALLOWED_REFLECTION_GETTER, get its value using reflection
                if (!empty($metadata->type['name']) && $this->isAllowedReflectionGetter($metadata->type['name'])) {
                    return ObjectAccess::getProperty(
                        $object,
                        $metadata->serializedName,
                        true
                    );
                }

                throw $error;
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
            ObjectAccess::setProperty($object, $metadata->serializedName, $value);

            return;
        }

        $object->{$metadata->setter}($value);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isAllowedReflectionGetter(string $type): bool
    {
        return in_array(
            $type,
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['typesWithAllowedReflectionGetter']
        );
    }
}
