<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\ContextBuilder;

use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;

class DeserializationContextBuilder extends AbstractContextBuilder
{
    /**
     * @return DeserializationContext
     */
    public static function create(): Context
    {
        return (DeserializationContext::create())
            ->enableMaxDepthChecks();
    }

    /**
     * @param OperationInterface $operation
     * @param Request $request
     * @param null $targetObject
     * @return DeserializationContext
     */
    public static function createFromOperation(OperationInterface $operation, Request $request, $targetObject = null): Context
    {
        $context = self::create();

        // There is a fallback to `normalizationContext` because of backward compatibility. Until version 1.2.x
        // `denormalizationContext` did not exist and same attributes were used for both contexts
        $attributes = $operation->getDenormalizationContext() ?? $operation->getNormalizationContext() ?? [];

        if (!empty($targetObject)) {
            $attributes['target'] = $targetObject;
        }

        $attributes = self::getCustomizedContextAttributes($operation, $request, $attributes);

        foreach ($attributes as $attributeName => $attributeValue) {
            $context->setAttribute($attributeName, $attributeValue);
        }

        return $context;
    }
}
