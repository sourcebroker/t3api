<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

class OpenApiSettings extends AbstractOperationResourceSettings
{
    protected string $entityName = '';

    protected string $tagName = '';

    protected string $tagDescription = '';

    protected string $schemaIdentifier = '';

    /**
     * @param OpenApiSettings|null $base
     * @return OpenApiSettings
     */
    public static function create(
        array $attributes = [],
        ?AbstractOperationResourceSettings $base = null,
        string $entityName = ''
    ): AbstractOperationResourceSettings {
        $settings = parent::create($attributes);
        $settings->entityName = $entityName;
        $settings->tagName = $attributes['tagName'] ?? $settings->tagName;
        $settings->tagDescription = $attributes['tagDescription'] ?? $settings->tagDescription;
        $settings->schemaIdentifier = $attributes['schemaIdentifier'] ?? $settings->schemaIdentifier;

        return $settings;
    }

    public function getTagName(): string
    {
        if ($this->tagName !== '') {
            return $this->tagName;
        }
        return $this->entityName;
    }

    public function getTagDescription(): string
    {
        if ($this->tagDescription !== '') {
            return $this->tagDescription;
        }
        return sprintf('Operations about %s', $this->entityName);
    }

    public function getSchemaIdentifierForMode(string $mode): string
    {
        if ($this->schemaIdentifier !== '') {
            return $this->schemaIdentifier . '__' . $mode;
        }
        return str_replace('\\', '.', $this->entityName) . '__' . $mode;
    }
}
