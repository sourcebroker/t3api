<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

use Symfony\Component\Routing\Route;

interface OperationInterface
{
    public function getKey(): string;

    public function getRoute(): Route;

    public function getPath(): string;

    public function getApiResource(): ApiResource;

    public function getMethod(): string;

    public function getNormalizationContext(): array;

    public function getSecurity(): string;

    public function getSecurityPostDenormalize(): string;

    public function getPersistenceSettings(): PersistenceSettings;

    public function isMethodGet(): bool;

    public function isMethodPut(): bool;

    public function isMethodPatch(): bool;

    public function isMethodPost(): bool;

    public function isMethodDelete(): bool;

    /**
     * @todo needed for now here for easier migration to OperationInterface,
     *     but seems should be refactored - maybe create another interface?
     */
    public function getUploadSettings(): UploadSettings;
}
