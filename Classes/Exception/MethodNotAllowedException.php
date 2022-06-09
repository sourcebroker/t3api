<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use ReflectionClass;
use ReflectionException;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Response;

class MethodNotAllowedException extends AbstractException
{
    public function __construct(OperationInterface $operation, int $code)
    {
        $this->title = self::translate('exception.method_not_allowed.title');

        try {
            $className = (new ReflectionClass($operation))->getShortName();
        } catch (ReflectionException $exception) {
            $className = self::class;
        }

        parent::__construct(
            self::translate(
                'exception.method_not_allowed.description',
                [
                    $operation->getMethod(),
                    $className,
                ]
            ),
            $code
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_METHOD_NOT_ALLOWED;
    }
}
