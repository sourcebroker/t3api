<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\OpenApi\Objects\Response as OpenApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Cross-origin request changing data from an origin not allowed by the CORS configuration, see `OriginValidationProcessor`.
 */
class OriginNotAllowedException extends AbstractException implements OpenApiSupportingExceptionInterface
{
    public static function getOpenApiResponse(): OpenApiResponse
    {
        return parent::getOpenApiResponse()
            ->statusCode(SymfonyResponse::HTTP_FORBIDDEN)
            ->description(self::translate('exception.origin_not_allowed.title'));
    }

    public function __construct(string $origin, string $method, int $code)
    {
        $this->title = self::translate('exception.origin_not_allowed.title');
        parent::__construct(self::translate('exception.origin_not_allowed.description', [$origin, $method]), $code);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
