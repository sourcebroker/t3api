<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response as OpenApiResponse;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class OperationNotAllowedException extends AbstractException implements OpenApiSupportingExceptionInterface
{
    public static function getOpenApiResponse(): OpenApiResponse
    {
        return parent::getOpenApiResponse()
            ->statusCode(SymfonyResponse::HTTP_NOT_FOUND)
            ->description(self::translate('exception.resource_not_found.title'));
    }

    public function __construct(OperationInterface $operation, int $code)
    {
        $this->title = self::translate('exception.operation_not_allowed.title');

        parent::__construct(
            self::translate(
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
