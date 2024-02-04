<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Construction;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ObjectConstructorChain
 */
class ObjectConstructorChain implements ObjectConstructorInterface
{
    /**
     * @var string[]
     */
    protected $constructors = [];

    /**
     * @var ObjectConstructorInterface[]|null
     */
    protected $constructorsInstances;

    /**
     * @param string[] $constructors
     */
    public function __construct($constructors)
    {
        $this->constructors = $constructors;
    }

    /**
     * @inheritDoc
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        foreach ($this->getConstructorsInstances() as $constructor) {
            $object = $constructor->construct($visitor, $metadata, $data, $type, $context);

            if ($object !== null) {
                return $object;
            }
        }

        throw new RuntimeException(sprintf('Could not construct object `%s`', $metadata->name), 1577822761813);
    }

    /**
     * @return ObjectConstructorInterface[]
     */
    protected function getConstructorsInstances(): array
    {
        if ($this->constructorsInstances === null) {
            $this->constructorsInstances = [];
            foreach ($this->constructors as $constructor) {
                $this->constructorsInstances[] = GeneralUtility::makeInstance($constructor);
            }
        }

        return $this->constructorsInstances;
    }
}
