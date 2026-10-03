<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\OpenApi\Objects\Response;

interface OpenApiSupportingExceptionInterface
{
    public static function getOpenApiResponse(): Response;
}
