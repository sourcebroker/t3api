<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use Exception;
use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractException extends Exception implements ExceptionInterface
{
    public function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * @VirtualProperty("hydra:title")
     */
    public function getTitle(): string
    {
        return $this->title ?? Response::$statusTexts[$this->getStatusCode()] ?? '';
    }

    /**
     * @VirtualProperty("hydra:description")
     */
    public function getDescription(): string
    {
        return $this->message ?? '';
    }
}
