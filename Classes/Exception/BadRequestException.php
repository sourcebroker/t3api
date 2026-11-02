<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\OpenApi\Objects\Response as OpenApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Invalid data sent by the client (request body, query parameters, headers). Unlike unexpected exceptions,
 * the description is returned to the client in every application context.
 */
class BadRequestException extends AbstractException implements OpenApiSupportingExceptionInterface
{
    public static function getOpenApiResponse(): OpenApiResponse
    {
        return parent::getOpenApiResponse()
            ->statusCode(SymfonyResponse::HTTP_BAD_REQUEST)
            ->description(self::translate('exception.bad_request.title'));
    }

    /**
     * @param string $descriptionKey Key of the description in `locallang.xlf`, without the `exception.bad_request.` prefix
     */
    public function __construct(string $descriptionKey, array $arguments, int $code)
    {
        $this->title = self::translate('exception.bad_request.title');
        parent::__construct(self::translate('exception.bad_request.' . $descriptionKey, $arguments), $code);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
