<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use SourceBroker\T3api\Annotation\ORM\Cascade;
use SourceBroker\T3api\Exception\BadRequestException;
use SourceBroker\T3api\Service\PropertyInfoService;
use SourceBroker\T3api\Service\SerializerService;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class AbstractDomainObjectHandler extends AbstractHandler implements DeserializeHandlerInterface
{
    use InlineRelationServiceTrait;

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
                // `0` clears a single relation (in a collection it is rejected by `ObjectStorageHandler`).
                if ((int)$data === 0) {
                    return null;
                }

                $object = $this->persistenceManager->getObjectByIdentifier(
                    (int)$data,
                    $targetObjectType,
                    false
                );
                if (!$object instanceof AbstractDomainObject) {
                    throw new BadRequestException(
                        'relation_not_found',
                        [(string)$data, implode('.', $context->getCurrentPath())],
                        1793670001
                    );
                }
                $this->getInlineRelationService()->assertAssignable($object, $context, 1793660001);

                return $object;
            }

            if (is_array($data)) {
                return $this->processCascadePersistence($data, $targetObjectType, $context);
            }

            throw new BadRequestException(
                'invalid_relation',
                [gettype($data), implode('.', $context->getCurrentPath())],
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

        // UID `0` is a new record as well, same as in `FileReferenceHandler`.
        if (!empty($data['uid'])) {
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

        if (!$object instanceof AbstractDomainObject) {
            throw new BadRequestException(
                'relation_not_found',
                [(string)$uid, implode('.', $context->getCurrentPath())],
                1588278656829
            );
        }

        if (!$this->isObjectInContextScope($context, $propertyMetadata, $object)) {
            throw new BadRequestException(
                'relation_not_assignable',
                [(string)$uid, implode('.', $context->getCurrentPath())],
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

    /**
     * Relations stored in the related table are checked by `InlineRelationService`. Other relations are compared with
     * the state loaded from the database (clean state) - the current value may be already reset (PUT).
     */
    private function isObjectInContextScope(
        DeserializationContext $context,
        PropertyMetadata $propertyMetadata,
        AbstractDomainObject $object
    ): bool {
        $parentObject = $this->getInlineRelationService()->getContextParent($context);
        if ($parentObject === null) {
            return false;
        }

        $assignable = $this->getInlineRelationService()->checkAssignable($parentObject, $propertyMetadata->name, $object);
        if ($assignable !== null) {
            return $assignable;
        }

        $property = $parentObject->_getCleanProperty($propertyMetadata->name);

        // Compared by UID - the related object may be an instance of another class (e.g. an API resource class
        // declared as `type` in YAML metadata) than the one in the relation.
        if ($property instanceof ObjectStorage) {
            foreach ($property as $relatedObject) {
                if ($relatedObject instanceof AbstractDomainObject && $relatedObject->getUid() === $object->getUid()) {
                    return true;
                }
            }

            return false;
        }

        // `LazyLoadingProxy::getUid()` does not load the related object.
        if ($property instanceof AbstractDomainObject || $property instanceof LazyLoadingProxy) {
            return $object->getUid() === $property->getUid();
        }

        return false;
    }
}
