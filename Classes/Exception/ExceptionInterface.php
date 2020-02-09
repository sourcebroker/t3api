<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;

interface ExceptionInterface
{
    public function getStatusCode(): int;
    public function getTitle(): ?string;
    public function getDescription(): ?string;
}
