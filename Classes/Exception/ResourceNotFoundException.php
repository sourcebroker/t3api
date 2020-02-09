<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use Symfony\Component\HttpFoundation\Response;

class ResourceNotFoundException extends AbstractException
{
    public function getTitle(): string
    {
        return 'Resource not found';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
