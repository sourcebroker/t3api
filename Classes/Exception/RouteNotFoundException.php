<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use Symfony\Component\HttpFoundation\Response;

class RouteNotFoundException extends AbstractException
{
    public function __construct(int $code)
    {
        $this->title = self::translate('exception.route_not_found.title');
        parent::__construct(self::translate('exception.route_not_found.description'), $code);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
