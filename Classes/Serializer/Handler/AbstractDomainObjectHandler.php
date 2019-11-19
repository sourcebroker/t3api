<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use InvalidArgumentException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use SourceBroker\T3api\Domain\Repository\CommonRepository;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

/**
 * Class AbstractDomainObjectHandler
 */
class AbstractDomainObjectHandler extends AbstractHandler implements DeserializeHandlerInterface
{
    const TYPE = 'AbstractDomainObjectTransport';

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
                return CommonRepository::getInstanceForEntity($type['params']['targetType'])->findByUid((int)$data);
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
