<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\Handler;

use InvalidArgumentException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use RuntimeException;
use SourceBroker\T3api\Annotation\ORM\Cascade;
use SourceBroker\T3api\Service\PropertyInfoService;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class AbstractDomainObjectHandler
 */
class AbstractDomainObjectHandler extends AbstractHandler implements DeserializeHandlerInterface
{
    public const TYPE = 'AbstractDomainObjectTransport';

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $data
     * @param array $type
     * @param DeserializationContext $context
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

            throw new InvalidArgumentException(
                sprintf('It was not possible to deserialize %s into %s', gettype($data), AbstractDomainObject::class),
                1584866997736
            );
        }

        return null;
    }

    /**
     * @param array $data
     * @param string $targetObjectType
     * @param DeserializationContext $context
     * @return mixed
     */
    protected function processCascadePersistence(
        array $data,
        string $targetObjectType,
        DeserializationContext $context
    ) {
        $propertyMetadata = $context->getMetadataStack()->offsetGet(0);
        $propertyPath = implode('.', $context->getCurrentPath());

        if (isset($data['uid'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path `%s`: Cascade update is not supported yet. If you would like to persist NEW related entity pass the object without `uid` property. If you would like to create relation to already persisted entity - just send it\'s UID (integer values instead of object)',
                    $propertyPath
                ),
                1584867492373
            );
        }

        if (!$propertyMetadata instanceof PropertyMetadata) {
            throw new RuntimeException(
                sprintf(
                    'It was not possible to check if property `%s` allows cascade persistence',
                    $propertyPath
                ),
                1584951142196
            );
        }

        if (!PropertyInfoService::allowsCascadePersistence($propertyMetadata->class, $propertyMetadata->name)) {
            throw new RuntimeException(
                sprintf(
                    'Property in path `%s` does not allow cascade persistence. Make sure if you really want to allow it and, if so, add `%s("persist")` annotation to this property.',
                    $propertyPath,
                    Cascade::class
                ),
                1584950593298
            );
        }

        return $context->getNavigator()->accept(
            $data,
            [
                'name' => $targetObjectType,
                'params' => [
                    '_passDomainObjectTransport' => true
                ]
            ]
        );
    }
}
