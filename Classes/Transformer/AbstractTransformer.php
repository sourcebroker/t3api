<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\SerializationContext;

/**
 * Class AbstractTransformer
 */
abstract class AbstractTransformer
{
    /**
     * @var JsonSerializationVisitor
     */
    protected $visitor;

    /**
     * @var SerializationContext
     */
    protected $context;

    /**
     * @var array
     */
    protected $typeParams = [];

    /**
     * AbstractTransformer constructor.
     *
     * @param JsonSerializationVisitor $visitor
     * @param SerializationContext $context
     * @param array $typeParams
     */
    public function __construct(JsonSerializationVisitor $visitor, SerializationContext $context, $typeParams)
    {
        $this->visitor = $visitor;
        $this->context = $context;
        $this->typeParams = $typeParams;
    }

    /**
     * @param mixed $property
     * @param array $params
     *
     * @return mixed
     */
    abstract public function serialize($property);
}
