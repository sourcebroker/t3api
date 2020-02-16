<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;

interface OpenApiSupportingExceptionInterface
{
    public static function getOpenApiResponse(): Response;
}
