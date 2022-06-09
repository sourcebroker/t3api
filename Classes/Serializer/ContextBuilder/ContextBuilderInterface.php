<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\ContextBuilder;

use JMS\Serializer\Context;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;

interface ContextBuilderInterface
{
    public const SIGNAL_CUSTOMIZE_SERIALIZER_CONTEXT_ATTRIBUTES = 'customizeSerializerContextAttributes';

    public static function create(): Context;
    public static function createFromOperation(OperationInterface $operation, Request $request): Context;
}
