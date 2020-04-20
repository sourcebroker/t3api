<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\ContextBuilder;

use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use SourceBroker\T3api\Domain\Model\AbstractOperation;
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
     * @param AbstractOperation $operation
     * @param Request $request
     * @param null $targetObject
     * @return DeserializationContext
     */
    public static function createFromOperation(AbstractOperation $operation, Request $request, $targetObject = null): Context
    {
        $context = self::create();

        $attributes = $operation->getNormalizationContext() ?? [];

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
