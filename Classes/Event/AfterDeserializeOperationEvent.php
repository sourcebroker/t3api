<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Event;

use SourceBroker\T3api\Domain\Model\OperationInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

final class AfterDeserializeOperationEvent
{
    private OperationInterface $operation;

    private object $object;

    public function __construct(OperationInterface $operation, AbstractDomainObject $object)
    {
        $this->operation = $operation;
        $this->object = $object;
    }

    public function getOperation(): OperationInterface
    {
        return $this->operation;
    }

    public function getObject(): AbstractDomainObject
    {
        return $this->object;
    }

    public function setObject($object): void
    {
        $this->object = $object;
    }
}
