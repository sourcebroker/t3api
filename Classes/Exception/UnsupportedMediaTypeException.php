<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\OpenApi\Objects\Response as OpenApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UnsupportedMediaTypeException extends AbstractException implements OpenApiSupportingExceptionInterface
{
    public static function getOpenApiResponse(): OpenApiResponse
    {
        return parent::getOpenApiResponse()
            ->statusCode(SymfonyResponse::HTTP_UNSUPPORTED_MEDIA_TYPE)
            ->description(self::translate('exception.unsupported_media_type.title'));
    }

    /**
     * @param string $contentType Content type of the request, empty string when the header is missing
     */
    public function __construct(string $contentType, int $code)
    {
        $this->title = self::translate('exception.unsupported_media_type.title');
        parent::__construct(
            $contentType === ''
                ? self::translate('exception.unsupported_media_type.missing')
                : self::translate('exception.unsupported_media_type.description', [$contentType]),
            $code
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNSUPPORTED_MEDIA_TYPE;
    }
}
