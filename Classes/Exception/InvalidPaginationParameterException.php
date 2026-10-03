<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\OpenApi\Objects\Response as OpenApiResponse;
use Symfony\Component\HttpFoundation\Response;

class InvalidPaginationParameterException extends AbstractException implements OpenApiSupportingExceptionInterface
{
    public static function getOpenApiResponse(): OpenApiResponse
    {
        return parent::getOpenApiResponse()
            ->statusCode(Response::HTTP_BAD_REQUEST)
            ->description(self::translate('exception.invalid_pagination_parameter.title'));
    }

    public static function invalidPage(string $pageParameterName): self
    {
        return new self(
            self::translate('exception.invalid_pagination_parameter.page', [$pageParameterName]),
            1791043200
        );
    }

    public static function invalidItemsPerPage(string $itemsPerPageParameterName): self
    {
        return new self(
            self::translate('exception.invalid_pagination_parameter.items_per_page', [$itemsPerPageParameterName]),
            1791043201
        );
    }

    public static function pageWithZeroItemsPerPage(string $pageParameterName, string $itemsPerPageParameterName): self
    {
        return new self(
            self::translate(
                'exception.invalid_pagination_parameter.page_with_zero_items_per_page',
                [$pageParameterName, $itemsPerPageParameterName]
            ),
            1791043202
        );
    }

    final public function __construct(string $description, int $code)
    {
        $this->title = self::translate('exception.invalid_pagination_parameter.title');
        parent::__construct($description, $code);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
