<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response as OpenApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResourceNotFoundException extends AbstractException implements OpenApiSupportingExceptionInterface
{
    public static function getOpenApiResponse(): OpenApiResponse
    {
        return parent::getOpenApiResponse()
            ->statusCode(SymfonyResponse::HTTP_NOT_FOUND)
            ->description(self::translate('exception.resource_not_found.title'));
    }

    public function __construct(string $resourceType, int $uid, int $code)
    {
        $this->title = self::translate('exception.resource_not_found.title');
        parent::__construct(
            self::translate('exception.resource_not_found.description', [$resourceType, $uid]),
            $code
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
