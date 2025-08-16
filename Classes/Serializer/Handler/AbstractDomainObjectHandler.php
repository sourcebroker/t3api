<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use SourceBroker\T3api\Annotation\ORM\Cascade;
use SourceBroker\T3api\Attribute\AsSerializerHandler;
use SourceBroker\T3api\Service\PropertyInfoService;
use SourceBroker\T3api\Service\SerializerService;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

#[AsSerializerHandler]
class AbstractDomainObjectHandler extends AbstractHandler implements DeserializeHandlerInterface
{
    /**
     * @var string
     */
    public const TYPE = 'AbstractDomainObjectTransport';

    public function __construct(
        protected readonly PersistenceManager $persistenceManager,
        protected readonly SerializerService $serializerService
    ) {}

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @param mixed $data
     * @return mixed|object
     */
    public function deserialize(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context
    ) {
        if (
            $type['name'] === self::TYPE
            && !empty($type['params']['targetType'])
            && is_subclass_of($type['params']['targetType'], AbstractDomainObject::class)
        ) {
            $targetObjectType = $type['params']['targetType'];

            if (is_numeric($data)) {
                return $this->persistenceManager->getObjectByIdentifier(
                    (int)$data,
                    $targetObjectType,
                    false
                );
            }

            if (is_array($data)) {
                return $this->processCascadePersistence($data, $targetObjectType, $context);
            }

            throw new \InvalidArgumentException(
                sprintf('It was not possible to deserialize %s into %s', gettype($data), AbstractDomainObject::class),
                1584866997736
            );
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function processCascadePersistence(
        array $data,
        string $targetObjectType,
        DeserializationContext $context
    ) {
        $propertyMetadata = $context->getMetadataStack()->offsetGet(0);

        if (!$propertyMetadata instanceof PropertyMetadata) {
            throw new \RuntimeException(
                sprintf(
                    'It was not possible to check if property `%s` allows cascade persistence',
                    implode('.', $context->getCurrentPath())
                ),
                1584951142196
            );
        }

        if (!PropertyInfoService::allowsCascadePersistence($propertyMetadata->class, $propertyMetadata->name)) {
            throw new \RuntimeException(
                sprintf(
                    'Property in path `%s` does not allow cascade persistence. Make sure if you really want to allow it and, if so, add `%s("persist")` annotation to this property.',
                    implode('.', $context->getCurrentPath()),
                    Cascade::class
                ),
                1584950593298
            );
        }

        if (isset($data['uid'])) {
            return $this->processCascadeUpdate(
                (int)$data['uid'],
                $targetObjectType,
                $propertyMetadata,
                $data,
                $context
            );
        }

        return $this->processCascadeInsert($data, $targetObjectType, $context);
    }

    private function processCascadeInsert(array $data, string $targetObjectType, DeserializationContext $context)
    {
        return $context->getNavigator()->accept(
            $data,
            [
                'name' => $targetObjectType,
                'params' => [
                    '_skipDomainObjectTransport' => true,
                ],
            ]
        );
    }

    /**
     * @throws \JsonException
     */
    private function processCascadeUpdate(
        int $uid,
        string $targetObjectType,
        PropertyMetadata $propertyMetadata,
        array $data,
        DeserializationContext $context
    ) {
        $object = $this->persistenceManager->getObjectByIdentifier($uid, $targetObjectType, false);

        if ($object === null) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Path `%s`: Entity of type `%s` with UID `%s` could not be found',
                    implode('.', $context->getCurrentPath()),
                    $targetObjectType,
                    $uid
                ),
                1588278656829
            );
        }

        if (!$this->isObjectInContextScope($context, $propertyMetadata, $object)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Object in path `%s` is out of scope. You can not update objects which are not related already. To create new object pass it without `uid` property.',
                    implode('.', $context->getCurrentPath())
                ),
                1589811402076
            );
        }

        $deserializationContext = $this->cloneDeserializationContext($context, ['target' => $object]);

        return $this->serializerService->deserialize(
            json_encode($data, JSON_THROW_ON_ERROR),
            $targetObjectType,
            $deserializationContext
        );
    }

    private function isObjectInContextScope(
        DeserializationContext $context,
        PropertyMetadata $propertyMetadata,
        $object
    ): bool {
        $parentObject = $context->getAttribute('target');

        if (empty($parentObject)) {
            throw new \RuntimeException(
                'It is not possible to check if object is in context scope without parent object. This message should never be thrown',
                1589811502043
            );
        }

        $property = ObjectAccess::getProperty($parentObject, $propertyMetadata->name);

        if (
            $propertyMetadata->type['name'] === ObjectStorage::class
            || is_subclass_of($propertyMetadata->type['name'], ObjectStorage::class)
        ) {
            return $property->contains($object);
        }

        if ($property instanceof AbstractDomainObject) {
            return $object->getUid() === $property->getUid();
        }

        throw new \RuntimeException('Unsupported object type', 1589811431286);
    }
}
