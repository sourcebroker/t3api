<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\Domain\Model\AbstractOperation;
use Symfony\Component\HttpFoundation\Response;

class OperationNotAllowedException extends AbstractException
{
    public function __construct(AbstractOperation $operation, int $code)
    {
        $this->title = $this->translate('exception.operation_not_allowed.title');

        parent::__construct(
            $this->translate(
                'exception.operation_not_allowed.description',
                [$operation->getPath()]
            ),
            $code
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
