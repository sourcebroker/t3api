<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Exception;

interface ExceptionInterface
{
    public function getStatusCode(): int;
    public function getTitle(): ?string;
    public function getDescription(): ?string;
}
