<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use Symfony\Component\HttpFoundation\Response;

class ResourceNotFoundException extends AbstractException
{
    public function __construct(string $resourceType, int $uid)
    {
        $this->title = $this->translate('exception.resource_not_found.title');
        parent::__construct($this->translate('exception.resource_not_found.description', [$resourceType, $uid]));
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
