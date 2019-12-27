<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use InvalidArgumentException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
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
     *
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
            if (is_numeric($data)) {
                return $this->persistenceManager->getObjectByIdentifier(
                    (int)$data,
                    $type['params']['targetType'],
                    false
                );
            }

            if (is_array($data)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Cascade persistance is not supported. Domain object uid should be passed instead of complex object in path `%s`.',
                        implode('.', $context->getCurrentPath())
                    ),
                    1571242078103
                );
            }
        }
    }
}
