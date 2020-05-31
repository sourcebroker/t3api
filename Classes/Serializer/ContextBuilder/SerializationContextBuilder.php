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
    public static function create(): Context
    {
        return (SerializationContext::create())
            ->enableMaxDepthChecks()
            ->setSerializeNull(true);
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @return SerializationContext
     */
    public static function createFromOperation(OperationInterface $operation, Request $request): Context
    {
        $context = self::create();

        $attributes = $operation->getNormalizationContext() ?? [];
        $attributes = self::getCustomizedContextAttributes($operation, $request, $attributes);

        foreach ($attributes as $attributeName => $attributeValue) {
            $context->setAttribute($attributeName, $attributeValue);
        }

        return $context;
    }
}
