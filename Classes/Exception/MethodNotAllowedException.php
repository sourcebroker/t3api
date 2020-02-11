<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use ReflectionClass;
use SourceBroker\T3api\Domain\Model\AbstractOperation;
use Symfony\Component\HttpFoundation\Response;

class MethodNotAllowedException extends AbstractException
{
    public function __construct(AbstractOperation $operation, int $code)
    {
        $this->title = $this->translate('exception.method_not_allowed.title');

        parent::__construct(
            $this->translate(
                'exception.method_not_allowed.description',
                [
                    $operation->getMethod(), (new ReflectionClass($operation))->getShortName()
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
