<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractException extends Exception implements ExceptionInterface
{
    /**
     * @var int
     */
    protected static $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * @var string|null
     */
    protected $title;

    public function getStatusCode(): int
    {
        return static::$statusCode;
    }

    public function getTitle(): ?string
    {
        return $this->title ?? Response::$statusTexts[static::$statusCode];
    }

    public function getDescription(): ?string
    {
        return $this->message;
    }
}
