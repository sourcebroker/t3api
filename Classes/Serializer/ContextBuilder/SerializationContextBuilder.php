<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Serializer\ContextBuilder;

use JMS\Serializer\Context;
use JMS\Serializer\SerializationContext;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;

class SerializationContextBuilder extends AbstractContextBuilder
{
    /**
     * @return SerializationContext
     */
    public function create(): Context
    {
        return SerializationContext::create()
            ->enableMaxDepthChecks()
            ->setSerializeNull(true);
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     *
     * @return SerializationContext
     */
    public function createFromOperation(
        OperationInterface $operation,
        Request $request
    ): Context {
        $context = $this->create();

        $attributes = $operation->getNormalizationContext() ?? [];

        foreach ($attributes as $attributeName => $attributeValue) {
            $context->setAttribute($attributeName, $attributeValue);
        }

        $this->dispatchAfterCreateContextForOperationEvent(
            $operation,
            $request,
            $context
        );
        return $context;
    }
}
